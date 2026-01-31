<?php

namespace SimoneBianco\LaravelDedupMedia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallDedupMediaCommand extends Command
{
    protected $signature = 'dedup-media:install';

    protected $description = 'Install the Laravel Dedup Media package';

    public function handle(): void
    {
        $this->info('📦 Installing Laravel Dedup Media...');

        // 1. Publish Config
        $this->publishConfig();

        // 2. Publish Migrations
        $this->publishMigrations();

        $this->newLine();
        $this->info('✅ Laravel Dedup Media installed successfully!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Review config/dedup_media.php');
        $this->line('  2. Run: php artisan migrate');
        $this->line('  3. Add HasDedupMedia trait to your models');
    }

    protected function publishConfig(): void
    {
        $configFile = config_path('dedup_media.php');
        
        if (file_exists($configFile)) {
            $this->info('   Config file already exists.');
            return;
        }

        $sourceConfig = __DIR__ . '/../../../config/dedup_media.php';
        
        if (file_exists($sourceConfig)) {
            copy($sourceConfig, $configFile);
            $this->info('   ✓ Published config/dedup_media.php');
        } else {
            $this->warn('   Could not find source config file.');
        }
    }

    protected function publishMigrations(): void
    {
        $filesystem = new Filesystem;
        $stubPath = __DIR__ . '/../../../database/migrations';
        
        $migrations = [
            'create_dedup_media_table.php.stub' => 'create_dedup_media_table',
        ];

        $baseTime = time();
        $count = 0;

        foreach ($migrations as $stub => $name) {
            $source = "{$stubPath}/{$stub}";
            
            if (!file_exists($source)) {
                $this->warn("   Stub {$stub} not found.");
                continue;
            }

            if ($this->migrationExists($name)) {
                $this->info("   Migration {$name} already exists.");
                continue;
            }

            $timestamp = date('Y_m_d_His', $baseTime + $count);
            $target = database_path("migrations/{$timestamp}_{$name}.php");
            
            $filesystem->copy($source, $target);
            $this->info("   ✓ Published migration: {$name}");
            $count++;
        }
    }

    protected function migrationExists(string $name): bool
    {
        $files = glob(database_path("migrations/*_{$name}.php"));
        return count($files) > 0;
    }
}
