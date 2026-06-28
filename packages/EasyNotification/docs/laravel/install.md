---eonx_docs---
title: Laravel
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

[1]: https://laravel.com/docs/13.x/providers
