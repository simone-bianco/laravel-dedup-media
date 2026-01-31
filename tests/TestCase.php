<?php

namespace SimoneBianco\LaravelDedupMedia\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SimoneBianco\LaravelDedupMedia\LaravelDedupMediaServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelDedupMediaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Use SQLite in memory for testing
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure dedup media
        $app['config']->set('dedup_media.disk', 'local');
        $app['config']->set('dedup_media.directory', 'dedup-media');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
