<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use EonX\EasyIdentity\Implementations\Auth0\Auth0IdentityServiceFactory;
use EonX\EasyIdentity\Interfaces\IdentityServiceInterface;

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
     * @noinspection PhpMissingParentCallCommonInspection Parent implementation is empty.
     *
     * Register the services to use Auth0 as identity service.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-identity.php', 'easy-identity');

        $this->app->singleton(IdentityServiceInterface::class, static function (): IdentityServiceInterface {
            $factory = new Auth0IdentityServiceFactory();

            return $factory->create(\config('easy-identity.implementations.auth0'));
        });
    }
}


