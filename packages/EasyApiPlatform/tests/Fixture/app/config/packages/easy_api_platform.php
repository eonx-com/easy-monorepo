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
};
