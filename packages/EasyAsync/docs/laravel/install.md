---eonx_docs---
title: Laravel
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

    \EonX\EasyAsync\Laravel\EasyAsyncServiceProvider::class,
],
```

[1]: https://laravel.com/docs/13.x/providers
