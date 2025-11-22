<?php

declare(strict_types=1);

namespace Hith\LaravelEraser\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

trait HasAutoEraser
{
    use EraserHelpers;

    /**
     * Cache of discovered relation methods for model.
     *
     * @var array<string, ReflectionMethod[]>
     */
    protected array $relationCache = [];

    /**
     * Boot register deleting listener.
     */
    protected static function booted(): void
    {
        static::deleting(function (Model $model): void {
            if (! self::canStartOnDelete($model)) {
                return;
            }

            $model->startErasing($model);
        });
    }

    /**
     * Clear model relations without deleting the model.
     */
    public function clean(?Model $model = null): void
    {
        $model ??= $this;
        $this->startErasing($model);
    }

    /**
     * Entry point
     */
    private function startErasing(Model $model): void
    {
        $this->model = $model;

        if (! $this->canStartAutoDelete($model)) {
            $this->log(
                'Auto-discover is disabled for '.class_basename($model::class),
                'error'
            );

            return;
        }

        $this->AutoDelete($model);

        if (method_exists($model, 'resetDeletionRegistry')) {
            $model->resetDeletionRegistry();
        }
    }

    /**
     * Check if erasing can start.
     */
    private function canStartAutoDelete(Model $model): bool
    {
        $globalEnabled = $this->getConfig('eraser_autoDiscover', true);
        $modelEnabled = isset($model->eraserAutoDiscover)
            ? (bool) $model->eraserAutoDiscover
            : null;

        return $modelEnabled ?? $globalEnabled;
    }

    /**
     * Automatically delete relations discovered via reflection.
     */
    private function AutoDelete(Model $model): void
    {
        $modelKey = class_basename($model).':'.$model->getKey();
        if (isset($this->deletionRegistry[$modelKey])) {
            $this->log("Skipping already processed {$modelKey}", 'info');

            return;
        }
        $this->deletionRegistry[$modelKey] = true;

        $className = get_class($model);

        if (! isset($this->relationCache[$className])) {
            $this->relationCache[$className] = $this->discoverRelationMethods($model);
        }

        foreach ($this->relationCache[$className] as $method) {
            try {
                $relation = $method->invoke($model);

                if ($relation instanceof Relation && ! $this->isParentRelation($relation)) {
                    $this->performDeleteOrDetach($relation, $model, $method->getName());
                    $this->log("Processed relation '{$method->getName()}'", 'info');
                }
            } catch (Throwable $e) {
                $this->log(
                    "Error invoking relation '{$method->getName()}': {$e->getMessage()}",
                    'error'
                );
            }
        }
    }

    /**
     * Discover relation methods on the given model using reflection.
     *
     * @return ReflectionMethod[]
     */
    private function discoverRelationMethods(Model $model): array
    {
        $class = new ReflectionClass($model);
        $methods = [];

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (
                $this->isRelationMethod($method, $model)
                && $this->methodReturnRelation($method, $model)
            ) {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}
