---eonx_docs---
title: Installation
weight: 1000
---eonx_docs---

# Installation

## Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-bugsnag
```

## Symfony

### Register bundle

If you're using [Symfony Flex][2], then the bundle is automatically registered. If not, you can register the bundle
yourself:

```php
// config/bundles.php

return [
    // Other bundles ...

    EonX\EasyBugsnag\Bundle\EasyBugsnagBundle::class => ['all' => true],
];
```

### Configuration

The minimum configuration required is your Bugsnag Integration API key. See [Configuration](config.md) for more
information about configuration options.

## Laravel

### Package service provider

In a Laravel application, you must tell your application to use the package. Laravel uses service providers to do this
(see [Service Providers][3] in the Laravel documentation for more information).

For example:

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyBugsnag\Laravel\EasyBugsnagServiceProvider::class,
],
```

### Configuration

The minimum configuration required is your Bugsnag Integration API key. See [Configuration](config.md) for more
information about configuration options.

## Lumen

### Register service provider

In a Lumen application you must explicitly tell the application to register the package's service provider as follows:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyBugsnag\Laravel\EasyBugsnagServiceProvider::class);
```

### Configuration

In Lumen, you must explicitly call `configure()` for the package:

```php
// bootstrap/app.php

$app = new \Laravel\Lumen\Application(\realpath(\dirname(__DIR__)));

// ...

$app->configure('easy-bugsnag');

// ...
```

The minimum configuration required is your Bugsnag Integration API key. See [Configuration](config.md) for more
information about configuration options.

[1]: https://getcomposer.org/

[2]: https://symfony.com/doc/current/setup/flex.html

[3]: https://laravel.com/docs/10.x/providers
