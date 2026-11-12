<div align="center">
    <h1>EonX - EasyPagination</h1>
    <p>Provides a generic way to handle pagination data from clients.</p>
</div>

---

# FromHttpFoundationRequestPaginationResolver

This resolver will look for pagination data in the request query parameters:

- `<url>?page=1&perPage=15`
- `<url>?_page=1&_per_age=15`
- `<url>?number=1&size=15`
- `<url>?offset=0&limit=15`

# Usage

```php
use EonX\EasyPagination\Provider\PaginationConfigProvider;
use EonX\EasyPagination\Resolver\FromHttpFoundationRequestPaginationResolver;

// Request: <url>?page=2&perPage=30

$paginationConfigProvider = new PaginationConfigProvider('page', 1, 'perPage', 15); // Instantiate config according to your needs
$resolver = new FromHttpFoundationRequestPaginationResolver($paginationConfigProvider); // Instantiate the resolver with your config

$data = $resolver->resolve($request); // Then resolve the data for the given request

$data->getStart(); // 2
$data->getSize(); // 30
```

# Invalid and large values

A `page` or `perPage` below 1 (or not a number) is replaced with the configured default.

The page size a client can request is not limited by default. To limit it, set `per_page_max`; a larger `perPage` is
reduced to it:

```php
// config/packages/easy_pagination.php
return static function (EasyPaginationConfig $easyPaginationConfig): void {
    $easyPaginationConfig->pagination()
        ->perPageMax(100);
};
```

In Laravel, set the `PAGINATION_PER_PAGE_MAX` env variable (or `pagination.per_page_max` in `config/easy-pagination.php`).
