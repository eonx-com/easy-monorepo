---eonx_docs---
title: Laravel/Lumen
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

### Package Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation](https://laravel.com/docs/10.x/providers).

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyRandom\Laravel\EasyRandomServiceProvider::class,
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

$app->register(\EonX\EasyRandom\Laravel\EasyRandomServiceProvider::class);
```

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

You can configure the UUID version to use for the `EonX\EasyRandom\Generator\UuidGeneratorInterface` service.
The default value is `6`. The possible values are `1`, `4`, `6`, `7`.

