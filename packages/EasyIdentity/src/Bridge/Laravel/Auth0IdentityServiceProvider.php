<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\Auth0IdentityServiceFactory;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceInterface;

final class Auth0IdentityServiceProvider extends ServiceProvider
{
    /**
     * Publish configuration file.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-identity.php' => \base_path('config/easy-identity.php')
        ]);
    }

    /**
     * Register the services to use Auth0 as identity service.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-identity.php', 'easy-identity');

        $this->app->singleton(IdentityServiceInterface::class, function (): IdentityServiceInterface {
            return (new Auth0IdentityServiceFactory())->create(\config('easy-identity.implementations.auth0'));
        });
    }
}

\class_alias(
    Auth0IdentityServiceProvider::class,
    'StepTheFkUp\EasyIdentity\Bridge\Laravel\Auth0IdentityServiceProvider',
    false
);
