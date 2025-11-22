<?php

declare(strict_types=1);

namespace Hith\LaravelEraser\Facades;

use Hith\LaravelEraser\Eraser;
use Illuminate\Support\Facades\Facade;

final class Erase extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Eraser::class;
    }
}
