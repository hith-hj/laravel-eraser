<?php

arch()
	->expect('Hith\LaravelEraser')
	->toUseStrictTypes()
	->not->toUse(['die', 'dd', 'dump']);

arch()
	->expect('Hith\LaravelEraser\Traits')
	->toBeTraits();

arch()
	->expect('Hith\LaravelEraser\Interfaces')
	->toBeInterfaces();

arch()->preset()->security();
arch()->preset()->php();
