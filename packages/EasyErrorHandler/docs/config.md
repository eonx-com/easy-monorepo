---eonx_docs--- title: Configuration weight: 1001 ---eonx_docs---

# Configuration

You can configure global settings for the EasyErrorHandler package via a configuration file in your application.

## Configuration files

For Laravel applications, the EasyErrorHandler configuration file must be called `easy-error-handler.php` and be located
in the `config` directory.

For Symfony applications, the EasyErrorHandler configuration file can be a YAML, XML or PHP file located under the
`config/packages` directory, with a name like `easy_error_handler.<format>`. The root node of the configuration must be
called `easy_error_handler`.

## Configuration options

The common configuration options for Laravel and Symfony are as follows:

| Configuration                              | Default      | Description                                                             |
|--------------------------------------------|--------------|-------------------------------------------------------------------------|
| `bugsnag_enabled`                          | `true`       | Automatically register the error reporter for easy-bugsnag integration. |
| `bugsnag_ignored_exceptions`               | `[]`         | List of exceptions that will not be reported to Bugsnag.                |
| `bugsnag_threshold`                        | `null`       | Log level threshold for reporting to Bugsnag.                           |
| `error_codes_interface`                    | `null`       | Interface with all error codes.                                         |
| `logger_exception_log_levels`              | `[]`         | List of exceptions and their associated log levels.                     |
| `logger_ignored_exceptions`                | `[]`         | List of exceptions that will not be reported to Logger.                 |
| `response.code`                            | `code`       | Attribute name for exception code.                                      |
| `response.exception`                       | `exception`  | Attribute name for exception text.                                      |
| `response.extended_exception_keys.class`   | `class`      | Attribute name for exception class.                                     |
| `response.extended_exception_keys.file`    | `file`       | Attribute name for exception file.                                      |
| `response.extended_exception_keys.line`    | `line`       | Attribute name for exception line.                                      |
| `response.extended_exception_keys.message` | `message`    | Attribute name for exception message.                                   |
| `response.extended_exception_keys.trace`   | `trace`      | Attribute name for exception trace.                                     |
| `response.message`                         | `message`    | Attribute name for user-friendly exception message.                     |
| `response.sub_code`                        | `sub_code`   | Attribute name for exception sub-code.                                  |
| `response.time`                            | `time`       | Attribute name for exception timestamp.                                 |
| `response.violations`                      | `violations` | Attribute name for exception violations.                                |
| `use_default_builders`                     | `true`       | Use the default set of [error response builders](response-builders.md). |
| `use_default_reporters`                    | `true`       | Use the default set of [error reporters](reporters.md).                 |

Laravel has the following additional configuration option:

| Configuration           | Default | Description                                                           |
|-------------------------|---------|-----------------------------------------------------------------------|
| `use_extended_response` | `false` | Use extended error response containing exception message, trace, etc. |

Symfony has the following additional configuration options:

| Configuration                                                | Default    | Description                                                                                                                      |
|--------------------------------------------------------------|------------|----------------------------------------------------------------------------------------------------------------------------------|
| `bugsnag_ignore_exceptions_handled_by_api_platform_builders` | `true`     | Ignore validation errors handled by `\EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ApiPlatformErrorResponseBuilderInterface`  |
| `api_platform_builders`                                      | `true`     | If using [API Platform](https://api-platform.com/), override its inbuilt exception handling to use the EasyErrorHandler package. |
| `translation_domain`                                         | `messages` | Symfony translation domain.                                                                                                      |
| `verbose`                                                    | `false`    | Use extended error response containing exception message, trace, etc.                                                            |

## Example configuration files

### Symfony

In Symfony, you could have a configuration file called `easy_error_handler.php` that looks like the following:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Config\EasyErrorHandlerConfig;

return static function (EasyErrorHandlerConfig $easyErrorHandlerConfig): void {
    $easyErrorHandlerConfig
        ->bugsnagEnabled(false)
        ->bugsnagIgnoredExceptions([
            InvalidArgumentException::class,
        ])
        ->bugsnagThreshold(null)
        ->loggerExceptionLogLevels([
            InvalidArgumentException::class => 300,
        ])
        ->loggerIgnoredExceptions([
            \App\MyCustomException::class,
        ]);

    $response = $easyErrorHandlerConfig->response();
    $response
        ->code('custom_code')
        ->exception('custom_exception')
        ->message('custom_message')
        ->subCode('custom_sub_code')
        ->time('custom_time')
        ->violations('custom_violations');
    $extendedExceptionKeys = $response->extendedExceptionKeys();
    $extendedExceptionKeys
        ->class('custom_class')
        ->file('custom_file')
        ->line('custom_line')
        ->message('custom_message')
        ->trace('custom_trace');

    $easyErrorHandlerConfig->apiPlatformCustomSerializerExceptions()
        ->class(UnexpectedValueException::class)
        ->messagePattern('/This value is not a valid date\/time\./')
        ->violationMessage('violations.invalid_datetime');

    $easyErrorHandlerConfig->apiPlatformCustomSerializerExceptions()
        ->class(NotNormalizableValueException::class)
        ->messagePattern('/Failed to parse time string \(.*\) at position .* \(.*\): .*/')
        ->violationMessage('Some custom violation message for datetime parsing error.');
};
```

### Laravel

In Laravel, the `easy-error-handler.php` configuration file could look like the following:

```php
<?php
declare(strict_types=1);

return [
    'bugsnag_enabled' => true,
    'bugsnag_ignored_exceptions' => [
        \InvalidArgumentException::class,
    ],
    'bugsnag_threshold' => null,
    'logger_exception_log_levels' => [
        \InvalidArgumentException::class => 300,
    ],
    'logger_ignored_exceptions' => [
        \App\MyCustomException::class,
    ],
    'response' => [
        'code' => 'code',
        'exception' => 'exception',
        'extended_exception_keys' => [
            'class' => 'class',
            'file' => 'file',
            'line' => 'line',
            'message' => 'message',
            'trace' => 'trace',
        ],
        'message' => 'message',
        'sub_code' => 'sub_code',
        'time' => 'time',
        'violations' => 'violations',
    ],
    'use_default_builders' => true,
    'use_default_reporters' => true,
    'use_extended_response' => false,
];
```
