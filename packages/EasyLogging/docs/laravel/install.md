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

<br>

### Sensitive Data Sanitizer

The sensitive data sanitizer is enabled by default: `\EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor`
masks sensitive data in the log records. It requires the `eonx-com/easy-utils` service provider
(`\EonX\EasyUtils\Laravel\EasyUtilsServiceProvider`) to be registered, otherwise a `LogicException` is thrown when
the logger is created. To opt out:

```php
// config/easy-logging.php

return [
    'sensitive_data_sanitizer' => [
        'enabled' => false,
    ],
];
```

<br>

### Default Stream Handler

The default stream handler is configured as an "enableable" node, meaning the log level is nested under the handler
it belongs to:

```php
// config/easy-logging.php

use Monolog\Level;

return [
    'stream_handler' => [
        // Enabled by default, writes to php://stdout
        'enabled' => true,
        // Defaults to DEBUG
        'level' => Level::Info->value,
    ],
];
```

A boolean is a shorthand for `enabled`: `'stream_handler' => false` disables the handler.

The default stream handler is registered only when no other `HandlerConfigProviderInterface` is tagged in the
container.

[1]: https://laravel.com/docs/13.x/providers
