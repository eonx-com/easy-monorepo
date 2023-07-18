---eonx_docs---
title: Laravel/Lumen
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

### Package Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation][1].

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyRandom\Bridge\Laravel\EasyRandomServiceProvider::class,
],
```

<br>

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

### UUID V4 Generator

In the case you want to generate UUID V4 in your application, you will need to set the UUID V4 generator of your choice
onto the random generator instance. To do so, we recommend extending the random generator service as follows:

```php
// app/Providers/MyRandomServiceProvider.php

use EonX\EasyRandom\Generators\RamseyUuidV4Generator;use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;use Illuminate\Support\ServiceProvider;

class MyRandomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(
            RandomGeneratorInterface::class,
            static function (RandomGeneratorInterface $randomGenerator): RandomGeneratorInterface {
                return $randomGenerator->setUuidV4Generator(new RamseyUuidV4Generator());
            }
        );
    }
}
```

[1]: https://laravel.com/docs/5.8/providers
