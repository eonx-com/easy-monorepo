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

    \EonX\EasyLogging\Laravel\EasyLoggingServiceProvider::class,
],
```

This package will automatically define the logger for the default channel within the container as:

- `Psr\Log\LoggerInterface`
- `logger`

<br>

#### Lumen Actions Required

To install this package in a Lumen application the procedures are a bit different.

##### Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyLogging\Laravel\EasyLoggingServiceProvider::class);
```

<br>

### Configuration

There is no required configuration, but if you want to specify a custom default channel you can do it.

```php
// config/easy-logging.php

return [
    'default_channel' => 'my-default-channel',
];
```

<br>

#### Configuration in Lumen

In Lumen, you will need to explicitly call configure for this package:

```php
// bootstrap/app.php

$app = new \Laravel\Lumen\Application(\realpath(\dirname(__DIR__)));

// ...

$app->configure('easy-logging');

// ...
```

[1]: https://laravel.com/docs/10.x/providers
