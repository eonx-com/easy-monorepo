<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyErrorHandlerConfig;

return static function (EasyErrorHandlerConfig $easyErrorHandlerConfig): void {
    $easyErrorHandlerConfig
        ->translationDomain('violations')
        ->bugsnagEnabled(false);

    // $easyErrorHandlerConfig->apiPlatformDenormalizationExceptions()
    // ->class(UnexpectedValueException::class)
    // ->messagePattern('/This value is not a valid date\/time\./')
    // ->violationMessage('violations.invalid_datetime');
    //
    // $easyErrorHandlerConfig->apiPlatformDenormalizationExceptions()
    // ->class(NotNormalizableValueException::class)
    // ->messagePattern('/Failed to parse time string \(.*\) at position .* \(.*\): .*/')
    // ->violationMessage('Some custom violation message for datetime parsing error.');
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
};
