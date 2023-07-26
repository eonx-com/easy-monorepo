<div align="center">
    <h1>EonX - EasyPagination</h1>
    <p>Provides a generic way to handle pagination data from clients.</p>
</div>

---

You are wondering if this package could be useful to you, right? Here are some points to help you find out:

- You have an PHP application
- You have to handle pagination data from your clients
- You are sick of having to resolve the "page" and "perPage" (or however you want to call those parameters) again and again
- Or sick of hearing "The pagination doesn't work for the blog posts comments" and realise you have a typo in "parPage"
- ...

This package provides you with out-the-box tools to implement a generic, centralised and consistent pagination data
handling system to keep you away from any troubles :)

# Documentation

## Installation

The recommended way to install this package is to use [Composer][1].

```bash
$ composer require eonx-com/easy-pagination
```

## How it works

This package provides different "resolvers" which will extract the pagination data from a request according to your
configuration. To guarantee interoperability, the resolvers expect a [PSR7 ServerRequestInterface][2], if your project
uses it too then perfect!

## Resolvers

Can't you find your happiness in the existing resolvers? Please let us know or even better create a PR :)

### StartSize Resolvers

The "StartSize" resolvers assume your pagination is based on only 2 attributes to define the start and its size. What
are those attributes names or default values? This is up to you!
A `StartSizeConfigInterface` (and its default implementation) is here for you to define all that as you want.

Here are some examples of configuration you can have:

| start_attributes | start_default | size_attribute | size_default |
|------------------|---------------|----------------|--------------|
| page             | 1             | perPage        | 15           |
| _page            | 1             | _per_page      | 15           |
| number           | 1             | size           | 15           |
| offset           | 0             | limit          | 30           |

##### StartSize Resolvers List

- [StartSizeInQueryResolver](docs/resolvers/startsize_in_query_resolver.md)
- [StartSizeAsArrayInQueryResolver](docs/resolvers/startsize_as_array_in_query_resolver.md)

[1]: https://getcomposer.org/

[2]: https://www.php-fig.org/psr/psr-7/#15-server-side-requests
