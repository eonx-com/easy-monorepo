---eonx_docs---
title: 'Error response builders'
weight: 1005
---eonx_docs---

# Error response builders

The **error response builders** are used by the ErrorHandler's `render()` method to build the HTTP error response. See
[ErrorHandler](error-handler.md).

The error response builders implement `EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface`, which defines
the following methods:

- `buildData()`: provides `$data`, an array of raw data which will be encoded to generate the HTTP response body
- `buildHeaders()`: provides `$headers`, an array of headers to set on the generated HTTP response
- `buildStatusCode()`: provides `$statusCode`, the status code of the generated HTTP response

The ErrorHandler loops through the provided error response builders and calls the methods above on each one.

Error response builders are provided to the ErrorHandler via implementations of
`EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface`.

## Default builders

The following set of error response builders are provided to the ErrorHandler by default:

- **CodeBuilder**: Adds the exception code to the response body.
- **ExtendedExceptionBuilder**: Adds extended exception information to the response body, including the following:
    - class
    - file
    - line
    - message
    - trace
- **StatusCodeBuilder**: Sets the HTTP response status code to the exception's `$statusCode` property. For example, it
  would set the status code to 404 for a NotFoundException.
- **SubCodeBuilder**: Adds the exception sub-code to the response body.
- **TimeBuilder**: Adds a timestamp to the response body.
- **UserMessageBuilder**: Adds the exception's user-friendly message to the response body.
- **ViolationsBuilder**: Adds violations information to the response body if the exception implements
  `EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface` (such as
  [ValidationException](exceptions.md))
- **HttpExceptionBuilder**: For HTTP exceptions in Symfony applications (i.e. exceptions that implement
  `Symfony\Component\HttpKernel\Exception\HttpExceptionInterface`), sets the message in the response body to the
  exception's `$message` property and the HTTP response status code to the exception's `$statusCode` property.

## Custom builders

You can create your own custom error response builders and provide them to the ErrorHandler.

Create your own custom error response builders by implementing
`EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface`.

Provide your error response builders to the ErrorHandler by using
`EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface`. This interface defines the `getBuilders()`
method which returns a collection of your `EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface`
implementations. The ErrorHandler accepts a collection of all error response builder providers via its constructor.

For example, to provide your custom error response builder, StatusCodeBuilder, to the ErrorHandler, create a builder
provider implementing `EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface`:

```php
// src/Exception/Response/MyStatusCodeBuilderProvider.php

namespace App\Exception\Response;

use EonX\EasyErrorHandler\Builders\StatusCodeErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;

final class MyStatusCodeBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface>
     */
     public function getBuilders(): iterable
    {
        // Return the built-in status code builder, but don't hesitate to create your own!
        yield new StatusCodeErrorResponseBuilder();
    }
}
```
