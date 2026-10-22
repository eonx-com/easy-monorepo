---eonx_docs---
title: Symfony
weight: 1000
is_section: true
section_icon: fab fa-symfony
---eonx_docs---

### Register Bundles

The bundle is a thin layer on top of [symfony/monolog-bundle][2]: the `logger` service, the channels and the handlers
are owned by symfony/monolog-bundle, this package provides reusable services for it (the sensitive-data sanitizer
processor, the Bugsnag Monolog handler and a JSON formatter).

symfony/monolog-bundle is not installed with this package (the Laravel integration does not need it), so install it
in Symfony applications:

```bash
$ composer require symfony/monolog-bundle
```

If you're using [Symfony Flex][1], this step has been done automatically for you. If not, register the bundles
yourself — `MonologBundle` is required, the bundle throws a `LogicException` at container compilation without it
(`EasyUtilsBundle` is required by the sensitive data sanitizer, enabled by default, see below):

```php
// config/bundles.php

return [
    // Other bundles ...

    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    EonX\EasyUtils\Bundle\EasyUtilsBundle::class => ['all' => true],
    EonX\EasyLogging\Bundle\EasyLoggingBundle::class => ['all' => true],
];
```

<br>

### Configuration

There is no required configuration. All options with their default values:

```php
# config/packages/easy_logging.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_logging' => [
        'sensitive_data_sanitizer' => [
            'enabled' => true,
        ],
        'bugsnag_handler' => [
            'enabled' => false,
            // Defaults to WARNING
            'level' => null,
            // The main "app" channel only when empty
            'channels' => [],
        ],
    ],
]);
```

eonx-com packages that log to a dedicated channel (e.g. `easy_http_client`, `security`, `easy_doctrine`) register that
channel with symfony/monolog-bundle and resolve their logger from it.

<br>

### Monolog handlers

Configuring the monolog handlers is the application's responsibility — without any handler, records go to a
`NullHandler` and are silently dropped. A minimal configuration:

```php
# config/packages/monolog.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'monolog' => [
        'handlers' => [
            'main' => [
                'type' => 'stream',
                'path' => 'php://stderr',
                'formatter' => 'easy_logging.formatter.json',
            ],
        ],
    ],
]);
```

The `easy_logging.formatter.json` service is this package's `\EonX\EasyLogging\Formatter\JsonFormatter`. The only
difference from Monolog's own `monolog.formatter.json` is the record `datetime`: it is formatted without microseconds
(`Y-m-d\TH:i:sP`). Use it if your log pipeline relies on it.

<br>

### Sensitive data sanitizer

`sensitive_data_sanitizer` is enabled by default: `\EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor` is
registered as a `monolog.processor` (with the lowest priority so it runs last) and masks sensitive data in the record
message, context, extra and formatted output.

It requires `EonX\EasyUtils\Bundle\EasyUtilsBundle` to be registered (`eonx-com/easy-utils` is installed with this
package) and its sensitive data sanitizer not to be disabled; otherwise the container compilation fails with a
`LogicException`. To opt out, disable it explicitly:

```php
# config/packages/easy_logging.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_logging' => [
        'sensitive_data_sanitizer' => false,
    ],
]);
```

<br>

### Bugsnag handler

When `bugsnag_handler` is enabled (`enabled: true`, or the `bugsnag_handler` node set at all — like any Symfony
"enableable" node, `bugsnag_handler: true` is a shorthand for it), the bundle registers `\EonX\EasyLogging\MonologHandler\BugsnagMonologHandler` with
symfony/monolog-bundle automatically as a `service` handler named `easy_logging_bugsnag`, so no handler declaration is
needed on the application side:

```php
# config/packages/easy_logging.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Level;

return App::config([
    'easy_logging' => [
        'bugsnag_handler' => [
            'enabled' => true,
            'level' => Level::Error->value,
            // Optional: the channels to attach the handler to (the main "app" channel only when empty)
            'channels' => ['app', 'easy_http_client'],
        ],
    ],
]);
```

- `bugsnag_handler.enabled` and `bugsnag_handler.channels` are read while the container is being wired, so they must
  be compile-time values (literals or container parameters, not env vars).
- `bugsnag_handler.channels` accepts the same channel list as monolog-bundle handlers; lists split across several
  config files (e.g. `config/packages/` and `config/packages/prod/`) are merged. When empty, the handler is attached
  to the main `app` channel only — not to every channel on purpose: for instance the `request` channel logs every
  uncaught exception at `error`/`critical` level, which would duplicate the reports sent by EasyErrorHandler and
  report the 4xx exceptions it ignores. To attach the handler to more channels list them explicitly (as with any
  monolog-bundle handler, a listed channel must exist: declare it under `monolog.channels` or via a `monolog.logger`
  tag), or use an exclusion list such as `['!request']`.
- If the application already attaches a `BugsnagMonologHandler` service itself (a `service` handler in its `monolog`
  configuration, whatever the service id), the bundle does not register a second one.
- symfony/monolog-bundle does not allow configuring a `formatter` on `service` handlers, so the handler uses its
  default formatter: a `LineFormatter` with the `[%datetime%] %channel%.%level_name%: %message%` format (context and
  extra are not part of the report message, they are attached to the report as metadata). To customise it, re-declare
  the handler service in the application (application definitions win over the bundle ones) and call `setFormatter()`
  on it:

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
              inline_service(LineFormatter::class)->args(['%%channel%%.%%level_name%%: %%message%%']),
          ]);
  };
  ```

The severity sent to Bugsnag is resolved by `\EonX\EasyLogging\Resolver\BugsnagSeverityResolverInterface`, whose
default implementation is registered together with the handler and maps Monolog levels as follows:

| Monolog level | Bugsnag severity |
|---------------|------------------|
| `Error` and above | `error` |
| `Warning` | `warning` |
| `Notice` and below | `info` |

Register your own implementation of the interface in the application to change this mapping. If you disable
`bugsnag_handler` to own the handler service yourself, register the resolver (or your own implementation) as well.

[1]: https://symfony.com/doc/current/setup/flex.html

[2]: https://github.com/symfony/monolog-bundle
