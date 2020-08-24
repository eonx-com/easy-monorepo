<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel;

use Bugsnag\Client;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\ClientFactory;
use EonX\EasyBugsnag\Interfaces\ClientFactoryInterface;
use Illuminate\Support\ServiceProvider;

final class EasyBugsnagServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-bugsnag.php' => \base_path('config/easy-bugsnag.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-bugsnag.php', 'easy-bugsnag');

        $this->app->singleton(ClientFactoryInterface::class, function (): ClientFactoryInterface {
            return (new ClientFactory())
                ->setConfigurators($this->app->tagged(BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR));
        });

        $this->app->singleton(Client::class, function (): Client {
            return $this->app->make(ClientFactoryInterface::class)->create(\config('easy-bugsnag.api_key'));
        });
    }
}
