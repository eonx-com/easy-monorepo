---eonx_docs---
title: Lumen cache config
weight: 2001
---eonx_docs---

Laravel comes with a built-in feature to cache the configuration of the application. Unfortunately it is not the case
for Lumen, this package provides this cache configuration in a Lumen context.

<br>

### Register the CachedConfigurationServiceProvider

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyCore\Bridge\Laravel\Providers\CachedConfigServiceProvider::class);
```

<br>

### Cache the app configuration

To cache of the Lumen application you need to run a console command and... That's it!

```bash
$ php artisan config:cache
```

<br>

### Clear the cached configuration

To clear the cached configuration you need to run a console command and... That's it once again!

```bash
$ php artisan config:clear
```
