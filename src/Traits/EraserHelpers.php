<?php

declare(strict_types=1);

namespace Eraser\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Psr\Log\LoggerInterface;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Throwable;

trait EraserHelpers
{
    protected array $deletionRegistry = [];

    /**
     * Prevent duplicate processing during recursive deletes (process-lifetime registry).
     */
    private Model $model;

    public function resetDeletionRegistry(): void
    {
        $this->deletionRegistry = [];
    }

    private static function canStartOnDelete($model)
    {
        $globalEnabled = $model->getConfig('eraser_onDeleteStart', true);
        $modelEnabled = isset($model->eraserOnDeleteStart) ?
            (bool) $model->eraserOnDeleteStart : null;

        return $modelEnabled ?? $globalEnabled;
    }

    /**
     * Basic reflection filter: method defined on the model class, public, non-static, zero args.
     */
    private function isRelationMethod(ReflectionMethod $method, Model $model): bool
    {
        return
            $method->class === get_class($model) &&
            ! $method->isStatic() &&
            $method->isPublic() &&
            $method->getNumberOfParameters() === 0;
    }

    /**
     * Decide whether a reflected method declares a relation return type or is allowed explicitly.
     */
    private function methodReturnRelation(ReflectionMethod $method, Model $model): bool
    {
        $type = $method->getReturnType();

        if ($type === null) {
            if ($this->propExists('eraserRelationMethods') && is_array($model->eraserRelationMethods)) {
                return in_array($method->getName(), $model->eraserRelationMethods, true);
            }

            return false;
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            return false;
        }

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();

            if ($type->isBuiltin()) {
                return false;
            }

            // concrete relation classes (HasMany, HasOne, etc.)
            if (is_a($name, Relation::class, true) || $name === Relation::class) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Parent relations we do NOT delete the target of.
     */
    private function isParentRelation(Relation $relation): bool
    {
        $parentRelations = $this->getConfig('eraser_parent_relations', []);
        foreach ($parentRelations as $relationClass) {
            if ($relation instanceof $relationClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * Perform deletion or pivot detach. Uses loaded results to detach.
     */
    private function performDeleteOrDetach(Relation $relation, Model $model, ?string $methodName = null): void
    {
        foreach ($this->getConfig('eraser_manytomany_relations', []) as $class) {
            if ($relation instanceof $class) {
                try {
                    $related = $relation->getResults();

                    $keys = $related instanceof Collection
                        ? $related->modelKeys()
                        : ($related instanceof Model ? [$related->getKey()] : $relation->get()->modelKeys());

                    if (! empty($keys)) {
                        $relation->detach($keys);
                        $this->log('Detached relation: '.$methodName, 'info');
                    } else {
                        $this->log('No results for '.$methodName, 'info');
                    }
                } catch (Throwable $e) {
                    $this->log('failed to detach ('.$methodName."): {$e->getMessage()}", 'error');
                }
                break;
            }
        }

        try {
            $related = $relation->getResults();

            if ($related === null) {
                $this->log('No related models found for '.$methodName, 'info');

                return;
            }

            if ($this->mustBulkDelete($model, $methodName)) {
                $relation->delete();

                return;
            }

            $this->deleter($related);
        } catch (Throwable $e) {
            $this->log('No results for ('.$methodName."): {$e->getMessage()}", 'error');
        }
    }

    private function deleter($related): void
    {
        $deleteModel = function ($model) {
            try {
                $model->delete();
            } catch (Throwable $e) {
                $this->log('Error Delete ('.class_basename($model)."): {$e->getMessage()}", 'error');
            }
        };

        if ($related instanceof Collection) {
            $related->each(fn (Model $m) => $deleteModel($m));

            return;
        }

        if ($related instanceof Model) {
            $deleteModel($related);

            return;
        }
    }

    /**
     * Get denylist entries and check if method should be skipped.
     * Case-insensitive.
     * Rules supported:
     *  - exact match: 'updateRate'
     *  - prefix wildcard: 'update*'  => matches 'updateRate'
     *  - suffix wildcard: '*Rate'    => matches 'updateRate'
     **/
    private function isMethodDenied(string $methodName, Model $model): bool
    {
        $denylist = $this->getEraserDenylist($model);
        $mn = mb_strtolower($methodName);

        foreach ($denylist as $entry) {
            $e = mb_strtolower((string) $entry);

            // prefix wildcard: update*
            if (str_ends_with($e, '*')) {
                $prefix = rtrim($e, '*');
                if ($prefix === '' || str_starts_with($mn, $prefix)) {
                    return true;
                }

                continue;
            }

            // suffix wildcard: *rate
            if (str_starts_with($e, '*')) {
                $suffix = ltrim($e, '*');
                if ($suffix === '' || str_ends_with($mn, $suffix)) {
                    return true;
                }

                continue;
            }

            // exact match
            if ($mn === $e) {
                return true;
            }
        }

        return false;
    }

    /**
     * Default denylist; models may override by defining $eraserDenylist.
     */
    private function getEraserDenylist(Model $model): array
    {
        if (isset($model->eraserDenylist) && is_array($model->eraserDenylist)) {
            return $model->eraserDenylist;
        }

        return $this->getConfig('eraser_base_deny_list', []);
    }

    /**
     * Helper: convenience wrapper for checking model properties presence in a static closure context.
     */
    private function propExists(string $property, $model = null): bool
    {
        $model = $model === null ? $this : $model;

        if (! property_exists($model, $property)) {
            return false;
        }

        return true;
    }

    private function getConfig(string $name, $default = null)
    {
        if (! function_exists('config')) {
            return $default;
        }

        return config("eraser.$name", $default);
    }

    private function mustBulkDelete($model, $methodName)
    {
        return isset($model->eraserBulkDelete) &&
            is_array($model->eraserBulkDelete) &&
            in_array($methodName, $model->eraserBulkDelete);
    }

    /**
     * Centralized logging
     *
     * Respects package config and model overrides, and resolves a logger.
     *
     * Logger resolution:
     *
     * - model $eraserLogger (PSR-3 or callable)
     *
     * - config('eraser.log_channel') -> app('log')->channel(...)
     *
     * - fallback to error_log
     **/
    private function log(string $message, string $level = 'info'): void
    {
        if (! $this->canLog()) {
            return;
        }

        if (isset($this->model->eraserLogger)) {
            $logger = $this->model->eraserLogger;
            if ($logger instanceof LoggerInterface) {
                $logger->log($level, $message);

                return;
            }

            if (is_callable($logger)) {
                try {
                    $logger($message, $level);

                    return;
                } catch (Throwable $e) {
                    error_log('Eraser: logger callable failed: '.$e->getMessage());
                }
            }
        }

        $channel = $this->getConfig('eraser_log_channel', null);
        if (! empty($channel)) {
            try {
                $laravelLogger = app('log')->channel($channel);
                if ($laravelLogger instanceof LoggerInterface) {
                    $laravelLogger->log($level, $message);

                    return;
                }
            } catch (Throwable $e) {
                error_log('Eraser: resolving log channel failed: '.$e->getMessage());
            }
        }

        error_log(sprintf('[Eraser][%s] %s', mb_strtoupper($level), $message));
    }

    private function canLog()
    {
        $globalEnabled = $this->getConfig('eraser_logging', true);
        $modelEnabled = isset($this->model->eraserLogging) ?
            (bool) $this->model->eraserLogging : null;

        return $modelEnabled ?? $globalEnabled;
    }
}
