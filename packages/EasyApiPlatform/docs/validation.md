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
use Symfony\Config\EasyApiPlatformConfig;

return static function (EasyApiPlatformConfig $easyApiPlatformConfig): void {
    $easyErrorHandlerConfig = $easyApiPlatformConfig->easyErrorHandler();

    $easyErrorHandlerConfig->customSerializerExceptions()
        ->class(UnexpectedValueException::class)
        ->messagePattern('/This value is not a valid date\/time\./')
        ->violationMessage('violations.invalid_datetime');

    $easyErrorHandlerConfig->customSerializerExceptions()
        ->class(NotNormalizableValueException::class)
        ->messagePattern('/Failed to parse time string \(.*\) at position .* \(.*\): .*/')
        ->violationMessage('Some custom violation message for datetime parsing error.');

    $easyErrorHandlerConfig = $easyApiPlatformConfig->easyErrorHandler();
    $easyErrorHandlerConfig->reportExceptionsToBugsnag(true);
};


```
