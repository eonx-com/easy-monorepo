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

## Laravel

### Register service provider

In a Laravel application, you must tell your application to use the package by registering its service provider:

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyErrorHandler\Laravel\EasyErrorHandlerServiceProvider::class,
],
```

[1]: https://getcomposer.org/

[2]: https://symfony.com/doc/current/setup/flex.html
