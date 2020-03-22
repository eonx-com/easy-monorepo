<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Bridge\Laravel;

use EonX\EasyIdentity\Implementations\Auth0\Auth0IdentityServiceFactory;
use EonX\EasyIdentity\Interfaces\IdentityServiceInterface;
use Illuminate\Support\ServiceProvider;

final class Auth0IdentityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-identity.php' => \base_path('config/easy-identity.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-identity.php', 'easy-identity');

        $this->app->singleton(IdentityServiceInterface::class, static function (): IdentityServiceInterface {
            $factory = new Auth0IdentityServiceFactory();

            return $factory->create(\config('easy-identity.implementations.auth0'));
        });
    }
}
