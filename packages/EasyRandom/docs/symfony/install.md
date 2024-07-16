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

    EonX\EasyRandom\Bundle\EasyRandomBundle::class => ['all' => true],
];
```

<br>

### Configuration

```php
# config/packages/easy_random.php

<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyRandomConfig;

return static function (EasyRandomConfig $easyRandomConfig): void {
    $easyRandomConfig->uuidVersion(6);
};

```

You can configure the UUID version to use for the `EonX\EasyRandom\Generator\UuidGeneratorInterface` service.
The default value is `6`. The possible values are `4` and `6`.

The following classes will be used depending on the version you choose:

- Version 4: `EonX\EasyRandom\Generator\SymfonyUuidV4Generator` (the `EonX\EasyRandom\Generator\RamseyUuidV4Generator` class if the "symfony/uid" package is not installed)
- Version 6: `EonX\EasyRandom\Generator\SymfonyUuidV6Generator` (the `EonX\EasyRandom\Generator\RamseyUuidV6Generator` class if the "symfony/uid" package is not installed)

Of course, you can also create your own generator by implementing the `EonX\EasyRandom\Generator\UuidGeneratorInterface` interface
and register it in your container.

<br>

[1]: https://symfony.com/components/Symfony%20Flex
