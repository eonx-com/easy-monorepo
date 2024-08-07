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

[1]: https://symfony.com/doc/current/setup/flex.html
