---eonx_docs---
title: Installation
weight: 1000
---eonx_docs---

# Installation

## Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-error-handler
```

## Symfony

### Register bundle

If you're using [Symfony Flex][2], then the bundle is automatically registered. If not, you can register the bundle
yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyErrorHandler\Bundle\EasyErrorHandlerBundle::class => ['all' => true],
];
```

## Laravel/Lumen

### Register service provider

In a Lumen application you must explicitly tell the application to register the package's service provider as follows:

```php
# bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyErrorHandler\Laravel\EasyErrorHandlerServiceProvider::class);
```

### Add configuration

In a Lumen application you must explicitly tell the application to add the package's config as following:

```php
# bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->configure('easy-error-handler');
```

[1]: https://getcomposer.org/

[2]: https://flex.symfony.com/
