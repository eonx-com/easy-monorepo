<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Console\Commands\Lumen;

use Illuminate\Console\Command;

final class ClearConfigCommand extends Command
{
    /**
     * @var string
     */
    private $cachedConfigPath;

    public function __construct()
    {
        $this->signature = 'config:clear';
        $this->description = 'Remove the configuration cache file';

        parent::__construct();
    }

    public function handle(): void
    {
        if (\file_exists($this->cachedConfigPath)) {
            \unlink($this->cachedConfigPath);
        }

        $this->info('Configuration cache cleared!');
    }

    /**
     * @param \Laravel\Lumen\Application $laravel
     */
    public function setLaravel($laravel): void
    {
        $this->cachedConfigPath = $laravel->storagePath('cached_config.php');

        parent::setLaravel($laravel);
    }
}
