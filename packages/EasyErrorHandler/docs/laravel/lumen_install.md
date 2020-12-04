---eonx_docs---
title: Laravel/Lumen
weight: 2000
is_section: true
section_icon: fab fa-laravel
---eonx_docs---

### Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
# bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyErrorHandler\Bridge\Laravel\Provider\EasyErrorHandlerServiceProvider::class);
```

### Add Config

In a Lumen application you must explicitly tell the application to add the package's config as following:

```php
# bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->configure('easy-error-handler');
```

### Change Exception Handler

Instead of using the default `App\Exception\Handler` change the default exception handler on your `bootstrap/app.php`.

```php
// bootstrap/app.php
...
$app->singleton(
    \Illuminate\Contracts\Debug\ExceptionHandler::class,
    \EonX\EasyErrorHandler\Bridge\Laravel\ExceptionHandler::class
);
...
```

[1]: https://getcomposer.org/
