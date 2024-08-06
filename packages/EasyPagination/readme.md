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

### Resolvers List

- [FromHttpFoundationRequestPaginationResolver](docs/resolvers/from_http_foundation_request_pagination_resolver.md)

[1]: https://getcomposer.org/

[2]: https://www.php-fig.org/psr/psr-7/#15-server-side-requests
