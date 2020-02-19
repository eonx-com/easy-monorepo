<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Providers;

use EonX\EasyCore\Bridge\Laravel\Console\Commands\Lumen\CacheConfigCommand;
use EonX\EasyCore\Bridge\Laravel\Console\Commands\Lumen\ClearConfigCommand;
use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Symfony\Component\Finder\Finder;

final class CachedConfigServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    private $cachedConfigPath;

    /**
     * CachedConfigurationServiceProvider constructor.
     *
     * @param \Laravel\Lumen\Application $app
     * @param null|string $cachedConfigPath
     */
    public function __construct(Application $app, ?string $cachedConfigPath = null)
    {
        $this->cachedConfigPath = $cachedConfigPath ?? $app->storagePath('cached_config.php');

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        parent::__construct($app);
    }

    /**
     * Register the services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerCommands();

        /** @var \Laravel\Lumen\Application $app */
        $app = $this->app;

        // If cached config exists, use it
        if (\file_exists($this->cachedConfigPath)) {
            /** @var mixed[] $items */
            $items = require $this->cachedConfigPath;

            // Set config instance with cached config
            $app->instance('config', new Repository($items));

            // Update loadedConfigurations on app to avoid any call to configure() to look into filesystem again
            foreach ($items as $name => $value) {
                (function ($name): void {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $this->loadedConfigurations[$name] = true;
                })->bindTo($app, Application::class)($name);
            }

            return;
        }

        // If cached config doesn't exist, load config from files
        foreach ($this->getConfigFiles() as $configFile) {
            $app->configure($configFile->getFilenameWithoutExtension());
        }
    }

    /**
     * Get config files.
     *
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    private function getConfigFiles(): array
    {
        $finder = (new Finder())->in(\sprintf('%s/config', $this->app->basePath()))->files()->name('*.php');

        return $finder->hasResults() ? \iterator_to_array($finder) : [];
    }

    /**
     * Register commands.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        $this->commands([
            CacheConfigCommand::class,
            ClearConfigCommand::class
        ]);
    }
}
