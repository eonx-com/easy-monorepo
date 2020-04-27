<div align="center">
    <h1>EonX - EasyErrorHandler</h1>
    <p>Provides customizable ready-to-use error handler for Lumen applications.</p>
</div>

---

This document describes the steps to install this package into a [Laravel][1] and/or [Lumen][2] application.

# Require package (Composer)

Laravel uses [Composer][3] to manage its dependencies. You can require this package as following:

```bash
$ composer require eonx-com/easy-error-handler
```

# Usage

### Requirements

In order to use package's error handler you must:
- Have [API Formats package][4] installed in your application with:
    - `\EoneoPay\ApiFormats\Bridge\Laravel\Providers\ApiFormatsServiceProvider` registered in your `bootstrap/app.php`
    - `\EoneoPay\ApiFormats\Bridge\Laravel\Middlewares\ApiFormatsMiddleware` registered in your `bootstrap/app.php`

- Have the [EasyLogging][5] package installed in your application and have bound implementation for the `\EonX\EasyLogging\Interfaces\LoggerInterface::class`

- Bind the handler in the `bootstrap/app.php`:
    ```php
    $app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, \EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler::class);
    ```

### Configuration

The package allows you to configure an error response field names. Just copy the `src/Bridge/Laravel/config/easy-error-handler.php` to your config directory and adjust it for your needs (you can leave only the fields which names you want to override).

### Create an exception

Extend your exception from `\EonX\EasyCore\Exceptions\BaseException`.

If it's validation exception containing a list of errors to be shown in the response then extend from the `\EonX\EasyCore\Exceptions\ValidationException`.

### Verbosity

By default, an `application/json` response will be like this (note: `violations` is an optional field added when the exception is an instance of the `ValidationExceptionInterface`):
```json
{
    "code": 0,
    "message": "User-friendly exception message",
    "time": "2020-03-25T05:15:54Z",
    "violations": {
        "externalId": [
            "The external id field is required."
        ]
    }
}
```

For extended response add the `APP_DEBUG` env variable and set it to `true`:

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
    "message": "User-friendly error message",
    "time": "2020-03-25T05:15:54Z"
}
```

### Message and exception message

`message` field (from the example above) must be used for user-friendly messages without any sensitive details as opposed to `exception.message`.
By default, it will show `Oops, something went wrong` message. However, you can control it:
- You can override `\EonX\Exceptions\Handler\BaseException::$userMessage` in your exception class
- You can control your message right in the place of throwing, for example:
```php
throw (new TestException(...))
    ->setUserMessage(':entity is not found.')
    ->setUserMessageParams(['entity' => 'Account']);
```

### Status code

By default, all exceptions will cause 500 status code. However, you can control it by extending your app exceptions from the following classes:
- `\EonX\EasyErrorHandler\Exceptions\BadRequestException` for 400 status code
- `\EonX\EasyErrorHandler\Exceptions\ConflictException` for 409 status code
- `\EonX\EasyErrorHandler\Exceptions\ForbiddenException` for 403 status code
- `\EonX\EasyErrorHandler\Exceptions\NotFoundException` for 404 status code
- `\EonX\EasyErrorHandler\Exceptions\UnauthorizedException` for 401 status code

### Log level

By default, all exceptions will be logged with the ERROR level. If you want you exception to be logged with different log level, then just override the `\App\Exceptions\Handler\BaseException::$logLevel` in your exception class.
You also can control log level right in an application code, for example:
```
throw (new TestException(...))->setLogLevel(LoggerInterface::LEVEL_CRITICAL);
```
 
### How to extend

Create your own handler extending the `\EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler` and override anything you need, for example:
- `\EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler::getResponseStatusCode` protected method with your custom status code determining logic
- `\EonX\EasyErrorHandler\Bridge\Laravel\Handler\Handler::$dontReport` protected property with your own list of non-reported exceptions

[1]: https://laravel.com/
[2]: https://lumen.laravel.com/
[3]: https://getcomposer.org/
[4]: https://github.com/eonx-com/apiformats
[5]: https://github.com/eonx-com/easy-logging
