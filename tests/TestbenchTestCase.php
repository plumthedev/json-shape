<?php

declare(strict_types=1);

namespace Plumthedev\JsonShape\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Base test case for feature tests that need a booted Laravel application,
 * the package migrations, and a real (in-memory) database connection.
 */
abstract class TestbenchTestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        /** @var Application $app */
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../workbench/database/migrations');
    }
}
