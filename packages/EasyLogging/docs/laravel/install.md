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

    \EonX\EasyLogging\Laravel\EasyLoggingServiceProvider::class,
],
```

This package will automatically define the logger for the default channel within the container as:

- `Psr\Log\LoggerInterface`
- `logger`

<br>

### Configuration

There is no required configuration, but if you want to specify a custom default channel you can do it.

```php
// config/easy-logging.php

return [
    'default_channel' => 'my-default-channel',
];
```

[1]: https://laravel.com/docs/13.x/providers
