<div align="center">
    <h1>EonX - EasyApiToken</h1>
    <p>Tools to handle API tokens like a pro.</p>
</div>

---

This document describes the steps to install this package into a [Laravel][1] and/or [Lumen][2] application.

# Require package (Composer)

Laravel uses [Composer][3] to manage its dependencies. You can require this package as following:

```bash
$ composer require eonx/easy-api-token
```

# Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation][4].

```php
// config/app.php

'providers' => [
    // Other Service Providers...
    
    \EonX\EasyApiToken\Bridge\Laravel\EasyApiTokenServiceProvider::class
],
```

# Config

# Lumen Actions Required

To install this package in a Lumen application the procedures are a bit different.

## Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyApiToken\Bridge\Laravel\EasyApiTokenServiceProvider::class);
```

## Add Config

In a Lumen application you must explicitly tell the application to add the package's config as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->configure('easy-api-token');
```

[1]: https://laravel.com/
[2]: https://lumen.laravel.com/
[3]: https://getcomposer.org/
[4]: https://laravel.com/docs/5.7/providers
