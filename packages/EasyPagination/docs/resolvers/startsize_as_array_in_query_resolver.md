<div align="center">
    <h1>LoyaltyCorp - EasyPagination</h1>
    <p>Provides a generic way to handle pagination data from clients.</p>
</div>

---

# StartSizeAsArrayInQueryResolver

This resolver will look for pagination data in the request query parameters as well, but this time as an array:

- `<url>?page[page]=1&page[perPage]=15`
- `<url>?pagination[_page]=1&pagination[_per_age]=15`
- `<url>?page_data[number]=1&page_data[size]=15`
- `<url>?whatever[offset]=0&whatever[limit]=15`

# Usage

```php
use LoyaltyCorp\EasyPagination\Resolvers\Config\StartSizeConfig;
use LoyaltyCorp\EasyPagination\Resolvers\StartSizeInQueryResolver;

// Request: <url>?page[number]=2&page[size]=30

$config = new StartSizeConfig('number', 1, 'size', 15); // Instantiate config according to your needs

// Instantiate the resolver with your config and the name of the query parameter containing the pagination data
$resolver = new StartSizeInQueryResolver($config, 'page'); 

$data = ()->resolve($request); // Then resolve the data for the given request

$data->getStart(); // 2
$data->getSize(); // 30
```
