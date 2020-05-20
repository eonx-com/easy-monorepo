---eonx_docs---
title: Usage
weight: 2
---eonx_docs---

### Create an exception

Extend your exception from `\EonX\EasyCore\Exceptions\BaseException`.

If it's a validation exception containing a list of errors (violations) to be shown in the response, then extend it from `\EonX\EasyCore\Exceptions\ValidationException`.

### Verbosity

By default, an `application/json` response will look like this (note: `violations` is an optional field added when the exception is an instance of the `ValidationExceptionInterface`):
```json
{
    "code": 0,
    "message": "User-friendly exception message.",
    "sub_code": 0,
    "time": "2020-03-25T05:15:54Z",
    "violations": {
        "externalId": [
            "The external id field is required."
        ]
    }
}
```

Set the `EASY_ERROR_HANDLER_USE_EXTENDED_RESPONSE` env variable to `true` to see a more verbose response, for example:

```json
{
    "code": 0,
    "exception": {
        "class": "App\\Exceptions\\Handler\\TestException",
        "file": "/var/www/app/Http/Controllers/V1/HealthCheckController.php",
        "line": 19,
        "message": "Exception message for developer",
        "trace": [
            {
                 "class": "Laravel\\Lumen\\Application",
                 "file": "/var/www/public/index.php",
                 "function": "run",
                 "line": 28,
                 "type": "->"
             }
        ]
    },
    "message": "User-friendly error message.",
    "sub_code": 0,
    "time": "2020-03-25T05:15:54Z"
}
```

### Message and exception message

The `message` field (see the examples above) is designed to be used as a user-friendly message (without any sensitive details, as opposed to `exception.message`).
By default, it is set to `Oops, something went wrong.`. However, you can control it:
- You can override `\EonX\Exceptions\Handler\BaseException::$userMessage` in your exception class
- You can change the translation of the default message in `resources/lang/vendor/easy-error-handler/en/messages.php`
- Alternatively, you can pass your own message (and translation parameters, if needed) right from your application code, for example:
```php
throw (new EntityNotFoundException(...))
    ->setUserMessage(':entity is not found.')
    ->setUserMessageParams(['entity' => 'Account']);
```

### Status code

By default, all exceptions will cause the `500` HTTP status code. However, you can control it by extending your app exceptions from the following classes:
- `\EonX\EasyErrorHandler\Exceptions\BadRequestException` for the `400` status code
- `\EonX\EasyErrorHandler\Exceptions\ConflictException` for the `409` status code
- `\EonX\EasyErrorHandler\Exceptions\ForbiddenException` for the `403` status code
- `\EonX\EasyErrorHandler\Exceptions\NotFoundException` for the `404` status code
- `\EonX\EasyErrorHandler\Exceptions\UnauthorizedException` for the `401` status code

### Log level

By default, all exceptions will be logged with the ERROR level. If you want your exception to be logged with a different log level, then simply override `\App\Exceptions\Handler\BaseException::$logLevel` in your exception class.
Alternatively, you can control the log level right from your application code, for example:
```
throw (new TestException(...))->setLogLevel(LoggerInterface::LEVEL_CRITICAL);
```
 
### How to extend

Create your own handler extending `\EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler` and override anything you need, for example:
- The `\EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler::getResponseStatusCode` protected method with your custom status code determining logic
- The `\EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler::$dontReport` protected property with your own list of non-reported exceptions
