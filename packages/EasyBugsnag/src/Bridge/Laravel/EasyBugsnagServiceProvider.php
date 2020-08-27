<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel;

use Bugsnag\Client;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\Doctrine\DBAL\SqlLoggerConfigurator;
use EonX\EasyBugsnag\Bridge\Laravel\Request\LaravelRequestResolver;
use EonX\EasyBugsnag\ClientFactory;
use EonX\EasyBugsnag\Configurators\BasicsConfigurator;
use EonX\EasyBugsnag\Interfaces\ClientFactoryInterface;
use Illuminate\Support\ServiceProvider;

final class EasyBugsnagServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected static $configurators = [];

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-bugsnag.php' => \base_path('config/easy-bugsnag.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-bugsnag.php', 'easy-bugsnag');

        $this->registerClient();
        $this->registerConfigurators();
        $this->registerDoctrineOrm();
        $this->registerRequestResolver();
    }

    private function registerClient(): void
    {
        // Client Factory + Client
        $this->app->singleton(ClientFactoryInterface::class, function (): ClientFactoryInterface {
            return (new ClientFactory())
                ->setConfigurators($this->app->tagged(BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR))
                ->setRequestResolver($this->app->make(LaravelRequestResolver::class));
        });

        $this->app->singleton(Client::class, function (): Client {
            return $this->app->make(ClientFactoryInterface::class)->create(\config('easy-bugsnag.api_key'));
        });
    }

    private function registerConfigurators(): void
    {
        // Configurators
        foreach (static::$configurators as $configurator) {
            $this->app->singleton($configurator);
            $this->app->tag($configurator, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);
        }

        $this->app->singleton(BasicsConfigurator::class, function (): BasicsConfigurator {
            $basePath = $this->app->basePath();

            return new BasicsConfigurator(
                $basePath . '/app',
                $basePath,
                $this->app->environment()
            );
        });
        $this->app->tag(BasicsConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);
    }

    private function registerDoctrineOrm(): void
    {
        if (\config('easy-bugsnag.doctrine_orm', false) === false
            || \class_exists(EntityManagerInterface::class) === false) {
            return;
        }

        $this->app->extend(
            EntityManagerInterface::class,
            function (EntityManagerInterface $entityManager): EntityManagerInterface {
                $configurator = new SqlLoggerConfigurator($this->app->make(Client::class));
                $configurator->configure($entityManager);

                return $entityManager;
            }
        );
    }

    private function registerRequestResolver(): void
    {
        // Request Resolver
        $this->app->singleton(LaravelRequestResolver::class);
    }
}
