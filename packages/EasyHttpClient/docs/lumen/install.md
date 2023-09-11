---eonx_docs---
title: Laravel/Lumen
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

### Package Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are not familiar with this concept make sure to have a look at the [documentation][1].

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyHttpClient\Bridge\Laravel\Providers\EasyHttpClientServiceProvider::class,
],
```

<br>

#### Lumen Actions Required

To install this package in a Lumen application the procedures are a bit different.

<br>

##### Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyHttpClient\Bridge\Laravel\Providers\EasyHttpClientServiceProvider::class);
```

[1]: https://laravel.com/docs/5.8/providers
