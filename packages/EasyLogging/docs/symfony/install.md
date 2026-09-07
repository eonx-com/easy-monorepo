---eonx_docs---
title: Symfony
weight: 1000
is_section: true
section_icon: fab fa-symfony
---eonx_docs---

### Register Bundle

If you're using [Symfony Flex][1], this step has been done automatically for you. If not, you can register the bundle
yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyLogging\Bundle\EasyLoggingBundle::class => ['all' => true],
];
```

<br>

### Configuration

There is no required configuration, but if you want to specify a custom default channel you can do it.

```php
# config/packages/easy_logging.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyLoggingConfig;

return static function (EasyLoggingConfig $easyLoggingConfig): void {
    $easyLoggingConfig->defaultChannel('my-channel');
};

```

<br>

### Using symfony/monolog-bundle

Historically this package replaced [symfony/monolog-bundle][2] because the bundle did not support processor priorities.
Since [monolog-bundle 3.11.0][3] that feature is available, so you can now let symfony/monolog-bundle own the logger
configuration and keep using the reusable services provided by this package (e.g. the sensitive-data sanitizer processor
and the Bugsnag Monolog handler).

To do so, install `symfony/monolog-bundle` (`^3.11.1`), register `Symfony\Bundle\MonologBundle\MonologBundle`, and enable
the `use_symfony_monolog_bundle` option:

```php
# config/packages/easy_logging.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyLoggingConfig;

return static function (EasyLoggingConfig $easyLoggingConfig): void {
    $easyLoggingConfig->useSymfonyMonologBundle(true);
};
```

> The option must be a compile-time value — a literal boolean or a container parameter. It cannot depend on runtime
> environment variables because it switches how the container is wired. The same applies to `bugsnag_handler` and
> `bugsnag_handler_channels` (see below), which are read at the same stage.

When enabled:

- the channel-replacement and default stream-handler compiler passes of this package step aside so that
  symfony/monolog-bundle owns the `logger` service and the per-channel loggers;
- the `SensitiveDataSanitizerProcessor` is registered as a `monolog.processor` (with the lowest priority so it runs last);
- eonx-com packages that log to a dedicated channel (e.g. `easy_http_client`, `security`, `easy_doctrine`) register that
  channel with monolog-bundle automatically and resolve their logger from it.

Configuring the monolog handlers becomes the application's responsibility — without any handler, records go to a
`NullHandler` and are silently dropped. A minimal configuration:

```php
# config/packages/monolog.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\MonologConfig;

return static function (MonologConfig $monologConfig): void {
    $monologConfig->handler('main')
        ->type('stream')
        ->path('php://stderr')
        ->formatter('easy_logging.formatter.json');
};
```

The `easy_logging.formatter.json` service is this package's `\EonX\EasyLogging\Formatter\JsonFormatter`: unlike
Monolog's own `monolog.formatter.json`, it normalises `DateTimeInterface` values inside `context`/`extra` (as
`Y-m-d\TH:i:sP`). Use it to keep the log shape you had with the `LoggerFactory`.

<br>

### Bugsnag handler with symfony/monolog-bundle

When both `use_symfony_monolog_bundle` and `bugsnag_handler` are enabled, the bundle registers
`\EonX\EasyLogging\MonologHandler\BugsnagMonologHandler` with symfony/monolog-bundle automatically as a `service`
handler named `easy_logging_bugsnag`, so no handler declaration is needed on the application side:

```php
# config/packages/easy_logging.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Level;
use Symfony\Config\EasyLoggingConfig;

return static function (EasyLoggingConfig $easyLoggingConfig): void {
    $easyLoggingConfig->useSymfonyMonologBundle(true);
    $easyLoggingConfig->bugsnagHandler(true);
    $easyLoggingConfig->bugsnagHandlerLevel(Level::Error->value);
    // Optional: restrict the handler to some channels (attached to all channels when empty)
    $easyLoggingConfig->bugsnagHandlerChannels(['app', 'easy_http_client']);
};
```

- `bugsnag_handler` and `bugsnag_handler_channels` are read while the container is being wired, so, like
  `use_symfony_monolog_bundle`, they must be compile-time values (literals or container parameters, not env vars).
- `bugsnag_handler_level` keeps working: the handler service is still owned by this package.
- `bugsnag_handler_channels` accepts the same channel list as monolog-bundle handlers. Keep in mind that an
  exclusion such as `['!app']` excludes only `app` — records from any other channel that already has a dedicated
  handler will reach both handlers.
- The application may override the generated handler by declaring a handler with the same name
  (`easy_logging_bugsnag`) in its own `monolog` configuration.
- symfony/monolog-bundle does not allow configuring a `formatter` on `service` handlers, so the handler uses its
  default formatter. To customise it, re-declare the handler service in the application (application definitions win
  over the bundle ones) and call `setFormatter()` on it:

  ```php
  # config/packages/easy_logging_bugsnag.php

  <?php
  declare(strict_types=1);

  namespace Symfony\Component\DependencyInjection\Loader\Configurator;

  use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
  use Monolog\Formatter\LineFormatter;

  return static function (ContainerConfigurator $containerConfigurator): void {
      // "%" must be doubled in PHP config files, otherwise the string is treated as a parameter placeholder
      $containerConfigurator->services()
          ->set(BugsnagMonologHandler::class)
          ->autowire()
          ->arg('$level', param('easy_logging.bugsnag_handler_level'))
          ->call('setFormatter', [
              inline_service(LineFormatter::class)->args(['[%%datetime%%] %%channel%%.%%level_name%%: %%message%%']),
          ]);
  };
  ```

The severity sent to Bugsnag is resolved by `\EonX\EasyLogging\Resolver\BugsnagSeverityResolverInterface`, whose
default implementation is registered together with the handler and maps Monolog levels as follows:

| Monolog level | Bugsnag severity |
|---------------|------------------|
| `Critical` and above | `error` |
| `Error` | `warning` |
| `Warning` and below | `info` |

Register your own implementation of the interface in the application to change this mapping. If you disable
`bugsnag_handler` to own the handler service yourself, register the resolver (or your own implementation) as well.

> The `LoggerFactory` and the config-provider mechanism remain available but are deprecated and will be removed in `7.0`
> in favour of symfony/monolog-bundle.

[1]: https://symfony.com/doc/current/setup/flex.html

[2]: https://github.com/symfony/monolog-bundle

[3]: https://github.com/symfony/monolog-bundle/releases/tag/v3.11.0
