<!---eonx_docs---
title: Installation
weight: 1000
---eonx_docs--->

# Installation

The recommended way to install this package is to use [Composer](https://getcomposer.org/):

```bash
composer require eonx-com/easy-api-platform
```

## Register bundle

Your bundle should be automatically enabled by Flex.
In case you don't use Flex, you'll need to manually enable the bundle by adding the following line
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // Other bundles ...

    \EonX\EasyApiPlatform\Bundle\EasyApiPlatformBundle::class => ['all' => true],
];
```
