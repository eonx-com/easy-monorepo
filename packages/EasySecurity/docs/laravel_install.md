<div align="center">
    <h1>EonX - EasySecurity</h1>
    <p>Provides security features to be generic across applications.</p>
</div>

---

This document describes the steps to install this package into a [Laravel][1] and/or [Lumen][2] application.

# Require package (Composer)

Laravel uses [Composer][3] to manage its dependencies. You can require this package as following:

```bash
$ composer require eonx/easy-security
```

# Package Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation][4].

```php
// config/app.php

'providers' => [
    // Other Service Providers...
    
    \EonX\EasySecurity\Bridge\Laravel\EasySecurityServiceProvider::class
],
```

# Lumen Actions Required

To install this package in a Lumen application the procedures are a bit different.

## Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasySecurity\Bridge\Laravel\EasySecurityServiceProvider::class);
```

# Your own Service Provider

The services required for this package to work are the same as described in the [Symfony documentation][5].
The only difference is how to register them as services within your application. Here comes your Service Provider:

```php
namespace App\Providers;

use App\Services\Security\Factories\ContextFactory;
use App\Services\Security\Providers\InMemoryRolesProvider;
use App\Services\Security\Providers\ProviderProvider;
use App\Services\Security\Providers\UserProvider;
use EonX\EasySecurity\Bridge\TagsInterface;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;
use EonX\EasySecurity\Interfaces\UserProviderInterface;
use EonX\EasySecurity\Modifiers\ProviderFromHeaderModifier;
use EonX\EasySecurity\Modifiers\ProviderFromJwtModifier;
use EonX\EasySecurity\Modifiers\RolesFromJwtModifier;
use EonX\EasySecurity\Modifiers\UserFromJwtModifier;
use Illuminate\Support\ServiceProvider;

final class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register security services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ContextFactoryInterface::class, ContextFactory::class);
        $this->app->singleton(RolesProviderInterface::class, InMemoryRolesProvider::class);
        $this->app->singleton(ProviderProviderInterface::class, ProviderProvider::class);
        $this->app->singleton(UserProviderInterface::class, UserProvider::class);

        // DataResolvers
        $dataResolvers = [
            RolesFromJwtModifier::class,
            ProviderFromJwtModifier::class,
            ProviderFromHeaderModifier::class,
            UserFromJwtModifier::class
        ];

        foreach ($dataResolvers as $dataResolver) {
            $this->app->singleton($dataResolver);
            $this->app->tag($dataResolver, [TagsInterface::TAG_CONTEXT_DATA_RESOLVER]);
        }
    }
}
```

[1]: https://laravel.com/
[2]: https://lumen.laravel.com/
[3]: https://getcomposer.org/
[4]: https://laravel.com/docs/5.7/providers
[5]: symfony_install.md
