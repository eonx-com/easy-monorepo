---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

This package performs the following:

- Define a common structure of exceptions
- Generate consistent error responses for unhandled exceptions within the code
- By default, log them using the main logging channel of the app
- By default, and if used with [easy-bugsnag][0] will automatically notify bugsnag when required (based on log level of the exception)

<br>

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-error-handler
```

### Exceptions

- **BaseException:** parent exception for all the others
- **BadRequestException:** for 400 Bad Request HTTP responses
- **ConflictException:** for 409 Conflict HTTP responses
- **ErrorException:** for ERROR log level (e.g. any unexpected error)
- **ForbiddenException:** for 403 Forbidden HTTP responses
- **NotFoundException:** for 404 Not Found HTTP responses
- **UnauthorizedException:** for 401 Unauthorized HTTP responses
- **ValidationException:** extends `BadRequestException` with additional list of errors

<br>

### ErrorHandler

The `EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface` define 2 methods:

- **render:** generates an HTTP response for the given request and error
- **report:** generates reports for the given error (e.g. logging)

<br>

When using this package in your favourite framework, the `ErrorHandlerInterface` is registered as a service, so you can
use dependency injection to use it within your application:

```php
// src/Service/MyService.php

namespace App\Service;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;

final class MyService
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function doSomething(): void
    {
        try {
            // Do something...
        } catch (\Throwable $throwable) {
            $this->errorHandler->report($throwable);
        }  
    }
}
```

<br>

### ErrorResponseBuilderInterface

The `EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface` is used to build the information required to create
an HTTP response for the given error. It is responsible for providing:

- **data:** raw data as an array, will be encoded to generate the HTTP response content
- **headers:** array of headers to set on the generated HTTP response
- **status code:** the status code of the generated HTTP response

The error handler loop through the list of `ErrorResponseBuilderInterface` provided to build the error response data to 
generate the HTTP response.

How do you provide those error response builders? Using `EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface`!
This interface defines one method `getBuilders()` which returns a collection of `ErrorResponseBuilderInterface`. The
error handler accept via its constructor a collection of those error response builder providers allowing you to create
and provide your owns.

```php
// src/Exception/Response/MyStatusCodeBuilderProvider.php

namespace App\Exception\Response;

use EonX\EasyErrorHandler\Builders\StatusCodeBuilder;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;

final class MyStatusCodeBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface>
     */
     public function getBuilders(): iterable
    {
        // Return a built-in status code builder, but don't hesitate to create your own!
        yield new StatusCodeBuilder();
    }
}
```

<br>

### ErrorReporterInterface

The `EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface` is used by the error handler to delegate the report logic
to your application. The error handler will loop through the provided error reporters and call their `report()` method.

How do you provide those error reporters? Using `EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface`!
This interface defines one method `getReporters()` which returns a collection of `ErrorReporterInterface`. The
error handler accept via its constructor a collection of those error reporters providers allowing you to create
and provide your owns.

<br>

[0]: https://packages.eonx.com/projects/eonx-com/easy-bugsnag/
[1]: https://getcomposer.org/

