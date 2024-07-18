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

# Usage

## StartSize EasyPagination

The "StartSize" service providers will register 2 services as:

- `EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface`: Used to resolve the pagination data
- `EonX\EasyPagination\Interfaces\StartSizeDataInterface`: Resolved pagination data

That's it you're all setup! You're now able to resolve pagination data or automatically inject it anywhere you want,
using dependency injection or service locator (we strongly recommend to use the first one haha).

```php
// Dependency Injection
public function __construct(\EonX\EasyPagination\Interfaces\StartSizeDataInterface $data) {
    $this->data = $data;

    $data->getStart();
    $data->getSize();
}

// Service Locator
$app->make(\EonX\EasyPagination\Interfaces\StartSizeDataInterface::class);
```

# Config

To make sure to fit your needs this package comes with a configuration file you can customize as you which.

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Array in query attribute name
    |--------------------------------------------------------------------------
    |
    | This config is used to resolve the pagination data when it is expected
    | in the query parameters of the request as an array. This config is the
    | name of the query parameter containing the pagination data array.
    |
    | Example:
    | For this config as "page", the resolver will look in the query for:
    | "<your-url>?page[<number_attr>]=1&page[<size_attr>]=15"
    |
    */
    'array_in_query_attr' => \env('PAGINATION_ARRAY_IN_QUERY_ATTR', 'page'),

    /*
    |--------------------------------------------------------------------------
    | StartSize EasyPagination
    |--------------------------------------------------------------------------
    |
    | This config contains the names of the attributes to use to resolve the
    | start_size pagination data, and also their default values if not set
    | on the given request.
    |
    */
    'start_size' => [
        'start_attribute' => \env('PAGINATION_PAGE_START_ATTRIBUTE', 'page'),
        'start_default' => \env('PAGINATION_PAGE_START_DEFAULT', 1),
        'size_attribute' => \env('PAGINATION_PAGE_SIZE_ATTRIBUTE', 'perPage'),
        'size_default' => \env('PAGINATION_PAGE_SIZE_DEFAULT', 15)
    ]
];
```

[1]: https://laravel.com/

[2]: https://lumen.laravel.com/

[3]: https://getcomposer.org/
