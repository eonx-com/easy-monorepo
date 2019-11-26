<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use EonX\EasyCore\Console\Commands\Lumen\CacheConfigCommand;
use EonX\EasyCore\Console\Commands\Lumen\ClearConfigCommand;

final class CachedConfigurationServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    private const CACHED_CONFIG_PATH = 'cached_config.php';

    /** @noinspection PhpMissingParentCallCommonInspection */

    /**
     * Register the services.
     *
     * @return void
     */
    public function register(): void
    {
        /** @var \Laravel\Lumen\Application $app */
        $app = $this->app;
        $cachedConfig = $app->storagePath(self::CACHED_CONFIG_PATH);

        if ($app->runningInConsole()) {
            $this->commands([
                CacheConfigCommand::class,
                ClearConfigCommand::class
            ]);
        }

        if (\file_exists($cachedConfig)) {
            /** @noinspection PhpIncludeInspection */
            /** @noinspection UsingInclusionReturnValueInspection */
            $items = require $cachedConfig;
            /** @var mixed[] $items */
            /** @var \Illuminate\Config\Repository $repository */
            $repository = $app->make('config');
            foreach ($items as $name => $config) {
                if ($repository->has($name) === false) {
                    $repository->set($name, $config);
                    (function ($name) {
                        /** @noinspection PhpUndefinedFieldInspection */
                        $this->loadedConfigurations[$name] = true;
                    })->bindTo($app, Application::class)($name);
                }
            }

            return;
        }

        $app->register(ConfigurationServiceProvider::class);
    }
}
