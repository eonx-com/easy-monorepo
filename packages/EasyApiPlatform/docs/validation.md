---eonx_docs---
title: Configuration
weight: 1001
---eonx_docs---

# Integrating with EasyErrorHandler and EasyBugsnag

If you are using the EasyErrorHandler package in your application the EasyApiPlatform package will
automatically integrate with it. All validation and serialization exception (related to denormalization)
will be handled by the EasyErrorHandler package.

If you are using the EasyBugsnag package in your application you could send the validation and serialization exception to Bugsnag.

## Example configuration files

In Symfony, you could have a configuration file called `easy_api_platform.php` that looks like the following:

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

return App::config([
    'easy_api_platform' => [
        'easy_error_handler' => [
            'custom_serializer_exceptions' => [
                [
                    'class' => UnexpectedValueException::class,
                    'message_pattern' => '/This value is not a valid date\/time\./',
                    'violation_message' => 'violations.invalid_datetime',
                ],
                [
                    'class' => NotNormalizableValueException::class,
                    'message_pattern' => '/Failed to parse time string \(.*\) at position .* \(.*\): .*/',
                    'violation_message' => 'Some custom violation message for datetime parsing error.',
                ],
            ],
            'report_exceptions_to_bugsnag' => true,
        ],
    ],
]);


```
