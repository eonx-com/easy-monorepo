<div align="center">
    <h1>LoyaltyCorp - EasyIdentity</h1>
    <p>Tools to handle authentication like a pro.</p>
</div>

---

This document describes the steps to install this package into a [Laravel][1] and/or [Lumen][2] application.

# Require package (Composer)

Laravel uses [Composer][3] to manage its dependencies. You can require this package as following:

```bash
$ composer require loyaltycorp/easy-identity
```

# Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation][4].

This package provides you with several service providers which will register different third party identity services
implementations into the services container automatically. Make sure to register the one matching your need:

```php
// config/app.php

'providers' => [
    // Other Service Providers...
    
    // Register Auth0 implementation
    \LoyaltyCorp\EasyIdentity\Bridge\Laravel\Auth0IdentityServiceProvider::class
],
```

# Config

To allow this package to work with your own third party identity services you must let it know about your credentials. 
To do so you will use the configuration file `src/Bridge/Laravel/config/easy-identity.php`. 
Copy/Paste this file into your `config` folder and then update it with your own credentials.

```php
return [
    'implementations' => [
        'auth0' => [
            'client_id' => \env('AUTH0_CLIENT_ID'),
            'client_secret' => \env('AUTH0_CLIENT_SECRET'),
            'connection' => \env('AUTH0_CONNECTION', 'DEV'),
            'domain' => \env('AUTH0_DOMAIN', 'your-domain.auth0.com')
        ]
    ]
```

# Lumen Actions Required

To install this package in a Lumen application the procedures are a bit different.

## Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\LoyaltyCorp\EasyIdentity\Bridge\Laravel\Auth0IdentityServiceProvider::class);
```

## Add Config

In a Lumen application you must explicitly tell the application to add the package's config as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->configure('easy-identity');
```

[1]: https://laravel.com/
[2]: https://lumen.laravel.com/
[3]: https://getcomposer.org/
[4]: https://laravel.com/docs/5.7/providers