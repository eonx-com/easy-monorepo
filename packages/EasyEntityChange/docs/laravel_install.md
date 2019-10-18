<div align="center">
    <h1>LoyaltyCorp - EasyEntityChange</h1>
    <p>Provides an easy way to hook up logic in your entities lifecycle.</p>
</div>

---

This document describes the steps to install this package into a [Laravel][1] and/or [Lumen][2] application.

# Require package (Composer)

Laravel uses [Composer][3] to manage its dependencies. You can require this package as following:

```bash
$ composer require loyaltycorp/easy-entity-change
```

# Config

## Doctrine

To use this package within your application using Doctrine, you need to register the event subscriber. If you're using
the [Laravel Doctrine package][4] you just have to modify the doctrine config file:

```php
// config/doctrine.php

'managers' => [
    'default' => [
        // ...
    
        'subscribers' => [
            // Your other subscribers...
    
            \LoyaltyCorp\EasyEntityChange\Doctrine\EntityChangeSubscriber::class,
        ]
    ]
]

```

[1]: https://laravel.com/
[2]: https://lumen.laravel.com/
[3]: https://getcomposer.org/
[4]: https://laraveldoctrine.org
