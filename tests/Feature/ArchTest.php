<?php

declare(strict_types=1);

arch()
    ->expect('Eraser')
    ->toUseStrictTypes()
    ->not->toUse(['die', 'dd', 'dump']);

arch()
    ->expect('Eraser\Traits')
    ->toBeTraits();

arch()->preset()->security();
arch()->preset()->php();
