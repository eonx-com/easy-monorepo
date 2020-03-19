<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Console\Commands\Lumen;

use Illuminate\Console\Command;

final class CacheConfigCommand extends Command
{
    /**
     * @var string
     */
    private $bootstrapPath;

    /**
     * @var string
     */
    private $cachedConfigPath;

    public function __construct()
    {
        $this->signature = 'config:cache';
        $this->description = 'Create a cache file for faster configuration loading';

        parent::__construct();
    }

    public function handle(): void
    {
        $this->call('config:clear');
        $config = $this->getFreshConfiguration();

        \file_put_contents($this->cachedConfigPath, '<?php return ' . \var_export($config, true) . ';' . \PHP_EOL);

        try {
            /** @noinspection PhpIncludeInspection */
            require $this->cachedConfigPath;
        } catch (\Throwable $exception) {
            \unlink($this->cachedConfigPath);

            throw new \LogicException('Your configuration files are not serializable.', 0, $exception);
        }

        $this->info('Configuration cached successfully!');
    }

    /**
     * @param \Laravel\Lumen\Application $laravel
     */
    public function setLaravel($laravel): void
    {
        $this->cachedConfigPath = $laravel->storagePath('cached_config.php');
        $this->bootstrapPath = $laravel->basePath('bootstrap/app.php');

        parent::setLaravel($laravel);
    }

    /**
     * @return mixed[]
     */
    private function getFreshConfiguration(): array
    {
        $app = require $this->bootstrapPath;
        $app->boot();

        return $app->make('config')->all();
    }
}
