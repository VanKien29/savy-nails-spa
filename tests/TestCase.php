<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $this->guardTestDatabase($app);

        return $app;
    }

    protected function guardTestDatabase(Application $app): void
    {
        $expected = 'savy_nails_spa_testing';
        if (! $app->environment('testing') || $app['config']->get('database.default') !== 'mysql'
            || $app['config']->get('database.connections.mysql.database') !== $expected) {
            throw new \RuntimeException('Feature tests require the isolated MySQL database '.$expected.'.');
        }
        // This guard runs before RefreshDatabase can migrate or drop any tables.
        $connection = $app['db']->connection('mysql');
        if ($connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) !== 'mysql'
            || $connection->selectOne('SELECT DATABASE() AS database_name')->database_name !== $expected) {
            throw new \RuntimeException('Refusing to run tests against an unexpected database.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Test database IDs can match live media IDs. Never share upload paths.
        Storage::fake('local');
        Storage::fake('public');
        // Keep compiled views separate from concurrently running local preview/export requests.
        $path = storage_path('framework/testing/views');
        if (! is_dir($path)) {
            mkdir($path, 0775, true);
        }
        config(['view.compiled' => $path]);
    }
}
