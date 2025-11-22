<?php

declare(strict_types=1);

namespace Hith\LaravelEraser\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Throwable;

trait HasManualEraser
{
    use EraserHelpers;

    /**
     * Hook into the model's boot lifecycle.
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
     * Delete model relations without deleting the model.
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

        $this->log(sprintf(
            'Processing: %s[%s]',
            class_basename($model::class),
            $model->id
        ));

        if (! $this->canStartManualDelete($model)) {
            $this->log(
                "Missing 'eraserRelationsToDelete' on ".class_basename($model::class),
                'warning'
            );

            return;
        }

        $this->ManualDelete($model);

        if (method_exists($model, 'resetDeletionRegistry')) {
            $model->resetDeletionRegistry();
        }
    }

    /**
     * Check if erasing can start.
     */
    private function canStartManualDelete(Model $model): bool
    {
        return is_array($model->eraserRelationsToDelete)
            && count($model->eraserRelationsToDelete) > 0;
    }

    /**
     * Manual mode: delete only relations listed in $eraserRelationsToDelete.
     */
    private function ManualDelete(Model $model): void
    {
        foreach ($model->eraserRelationsToDelete as $relationName) {
            if (! method_exists($model, $relationName)) {
                $this->log(
                    "Relation '{$relationName}' not found on ".class_basename($model),
                    'error'
                );

                continue;
            }

            try {
                $relation = $model->{$relationName}();

                if (! $relation instanceof Relation) {
                    $this->log(
                        "Method '{$relationName}' did not return a Relation",
                        'warning'
                    );

                    continue;
                }

                if (! $this->isParentRelation($relation)) {
                    $this->log("Processing relation '{$relationName}'", 'info');

                    $this->performDeleteOrDetach($relation, $model, $relationName);

                    $this->log("Relation '{$relationName}' processed", 'info');

                    continue;
                }

                $this->log("Skipping parent relation '{$relationName}'", 'info');
            } catch (Throwable $e) {
                $this->log(
                    "Error deleting relation '{$relationName}': {$e->getMessage()}",
                    'error'
                );
            }
        }
    }
}
