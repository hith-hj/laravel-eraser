<?php

declare(strict_types=1);

namespace Eraser\Facades;

use Eraser\Eraser;
use Illuminate\Support\Facades\Facade;

final class Erase extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Eraser::class;
    }
}
