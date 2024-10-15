<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Tests\Stub\Enum\ErrorCode;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Config\EasyErrorHandlerConfig;

return static function (EasyErrorHandlerConfig $easyErrorHandlerConfig): void {
    $easyErrorHandlerConfig
        ->translationDomain('violations');

    $easyErrorHandlerConfig->bugsnag()
        ->enabled(false);

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

    $easyErrorHandlerConfig->exceptionCodes(NotFoundHttpException::class, ErrorCode::Code1);
};
