<?php

declare(strict_types=1);

namespace Eraser\Tests;

use Eraser\Providers\EraserServiceProvider;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Stringable;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EraserServiceProvider::class,
        ];
    }

    final public function setDB()
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Create schema
        Schema::create('users', function ($table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('posts', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('comments', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('post_id')->constrained('posts');
            $table->timestamps();
        });
    }

    final public function logger()
    {
        return new class implements \Psr\Log\LoggerInterface
        {
            public array $messages = [];

            public function log($level, $message, array $context = []): void
            {
                $this->messages[] = [$level, $message];
            }

            public function info(string|Stringable $message, array $context = []): void
            {
                $this->messages[] = ['info', $message];
            }

            public function critical(string|Stringable $message, array $context = []): void
            {
                $this->messages[] = ['critical', $message];
            }

            public function warning(string|Stringable $message, array $context = []): void
            {
                $this->messages[] = ['warning', $message];
            }

            public function alert(string|Stringable $message, array $context = []): void
            {
                $this->messages[] = ['alert', $message];
            }

            public function notice(string|Stringable $message, array $context = []): void
            {
                $this->messages[] = ['notice', $message];
            }

            public function error(string|Stringable $message, array $context = []): void
            {
                $this->messages[] = ['error', $message];
            }

            public function emergency(string|Stringable $message, array $context = []): void
            {
                $this->messages[] = ['emergency', $message];
            }

            public function debug(string|Stringable $message, array $context = []): void
            {
                $this->messages[] = ['debug', $message];
            }
        };
    }
}
