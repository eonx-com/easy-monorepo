---eonx_docs---
title: Laravel/Lumen
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

### Package Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation][1].

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyNotification\Laravel\EasyNotificationServiceProvider::class,
],
```

#### Lumen Actions Required

To install this package in a Lumen application the procedures are a bit different.

##### Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyNotification\Laravel\EasyNotificationServiceProvider::class);
```

### Configuration

The only required configuration is the API URL of the EonX Notification service your application is working with.

```php
// config/easy-notification.php

return [
    'api_url' => \env('EONX_NOTIFICATION_API_URL', 'https://api.url.com'),

    // You can optionally customise the expiry time for the cached config here.
    'config_expires_after' => 500, // Number of seconds
];
```

#### Configuration in Lumen

In Lumen, you will need to explicitly call configure for this package:

```php
// bootstrap/app.php

$app = new \Laravel\Lumen\Application(\realpath(\dirname(__DIR__)));

// ...

$app->configure('easy-notification');

// ...
```

[1]: https://laravel.com/docs/10.x/providers
