<?php

declare(strict_types=1);

namespace Hith\LaravelEraser;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Eloquent Relations Eraser Class.
 *
 * this is a class to be used to clear relations for model.
 *
 * it either use relations auto-discovery
 *
 * or relations must be specified on the model
 *
 * @param  string  $type  [manual, auto]
 */
final class Eraser
{
    public function __construct(public string $type = 'manual') {}

    public function type(string $type): self
    {
        if (! in_array($type, $this->allowedTypes(), true)) {
            throw new InvalidArgumentException("Invalid Eraser Type: {$type}");
        }
        $this->type = $type;

        return $this;
    }

    public function clean(Model $model)
    {
        return $this->eraser($model);
    }

    public function delete(Model $model)
    {
        $this->eraser($model);

        return $model->delete();
    }

    private function eraser(Model $model)
    {
        return $this->type === 'auto'
            ? (new AutoEraser)->clean($model)
            : (new ManualEraser)->clean($model);
    }

    private function allowedTypes()
    {
        return ['manual', 'auto'];
    }
}
