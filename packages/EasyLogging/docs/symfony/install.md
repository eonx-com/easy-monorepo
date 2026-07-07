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

When enabled:

- the channel-replacement and default stream-handler compiler passes of this package step aside so that
  symfony/monolog-bundle owns the `logger` service and the per-channel loggers;
- the `SensitiveDataSanitizerProcessor` is registered as a `monolog.processor` (with the lowest priority so it runs last);
- eonx-com packages that log to a dedicated channel (e.g. `easy_http_client`, `security`, `easy_doctrine`) register that
  channel with monolog-bundle automatically and resolve their logger from it.

> The `LoggerFactory` and the config-provider mechanism remain available but are deprecated and will be removed in `7.0`
> in favour of symfony/monolog-bundle.

[1]: https://symfony.com/doc/current/setup/flex.html

[2]: https://github.com/symfony/monolog-bundle

[3]: https://github.com/symfony/monolog-bundle/releases/tag/v3.11.0
