<div align="center">
    <h1>EonX - EasyPagination</h1>
    <p>Provides a generic way to handle pagination data from clients.</p>
</div>

---

This document describes the steps to install this package into a [Laravel][1] and/or [Lumen][2] application.

# Require package (Composer)

Laravel uses [Composer][3] to manage its dependencies. You can require this package as following:

```bash
$ composer require eonx/easy-pagination
```

# Service Providers

Based on your application and the way you want to handle pagination data from your clients, you will need to use a
specific resolver. To make your life easier, for each built-in resolver comes a ready to go Laravel service provider!
You just have to register the one you want:

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyPagination\Laravel\EasyPaginationServiceProvider::class,
],
```

[1]: https://laravel.com/

[2]: https://lumen.laravel.com/

[3]: https://getcomposer.org/
