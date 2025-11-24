<?php

declare(strict_types=1);

namespace Eraser\Providers;

use Eraser\Eraser;
use Illuminate\Support\ServiceProvider;

final class EraserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Eraser::class, function ($app) {
            return new Eraser;
        });
        $this->mergeConfigFrom(__DIR__.'/../config/eraser.php', 'eraser');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/eraser.php' => config_path('eraser.php'),
        ], 'eraser-config');
    }
}
