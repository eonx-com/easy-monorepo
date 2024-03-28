---eonx_docs---
title: Laravel/Lumen
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

### Package Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation](https://laravel.com/docs/5.8/providers).

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyRandom\Bridge\Laravel\EasyRandomServiceProvider::class,
],
```

#### Lumen Actions Required

To install this package in a Lumen application the procedures are a bit different.

##### Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyRandom\Bridge\Laravel\EasyRandomServiceProvider::class);
```

<br>

### Configuration

```php
// config/easy_random.php

<?php
declare(strict_types=1);

return [
    // Version of UUID to generate
    'uuid_version' => 6, // Default value
];
```

You can configure the UUID version to use for the `EonX\EasyRandom\Interfaces\UuidGeneratorInterface` service.
The default value is `6`. The possible values are `1`, `4`, `6`, `7`.

If the [UID Component](https://symfony.com/doc/current/components/uid.html) installed than `\Symfony\Component\Uid\Factory\UuidFactory` uses for generating UUIDs.

Of course, you can also create your own generator by implementing the `EonX\EasyRandom\Interfaces\UuidGeneratorInterface` interface
and register it in your service provider.
