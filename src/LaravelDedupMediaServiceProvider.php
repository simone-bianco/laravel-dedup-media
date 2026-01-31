<?php

namespace SimoneBianco\LaravelDedupMedia;

use Illuminate\Filesystem\Filesystem;
use SimoneBianco\LaravelDedupMedia\Contracts\Hasher;
use SimoneBianco\LaravelDedupMedia\Contracts\PathGenerator;
use SimoneBianco\LaravelDedupMedia\Services\DedupMediaService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelDedupMediaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-dedup-media')
            ->hasConfigFile('dedup_media')
            ->hasCommand(Console\Commands\InstallDedupMediaCommand::class);
    }

    public function packageRegistered(): void
    {
        // Bind hasher from config
        $this->app->bind(Hasher::class, function ($app) {
            $hasherClass = config('dedup_media.hasher');
            return new $hasherClass();
        });

        // Bind path generator from config
        $this->app->bind(PathGenerator::class, function ($app) {
            $generatorClass = config('dedup_media.path_generator');
            return new $generatorClass();
        });

        // Bind the service
        $this->app->singleton('dedup-media', function ($app) {
            return new DedupMediaService(
                $app->make(Hasher::class),
                $app->make(PathGenerator::class)
            );
        });

        // Alias for dependency injection
        $this->app->alias('dedup-media', DedupMediaService::class);
    }
}
