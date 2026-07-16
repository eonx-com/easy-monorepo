<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Tests\Stub\Enum\ErrorCode;
use EonX\EasyErrorHandler\Tests\Stub\Exception\DummyExceptionInterface;
use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return App::config([
    'easy_error_handler' => [
        'translation_domain' => 'violations',
        'bugsnag' => false,
        'response' => [
            'code' => 'custom_code',
            'exception' => 'custom_exception',
            'message' => 'custom_message',
            'sub_code' => 'custom_sub_code',
            'time' => 'custom_time',
            'violations' => 'custom_violations',
            'extended_exception_keys' => [
                'class' => 'custom_class',
                'file' => 'custom_file',
                'line' => 'custom_line',
                'message' => 'custom_message',
                'trace' => 'custom_trace',
            ],
        ],
        'exception_to_status_code' => [
            DummyExceptionInterface::class => HttpStatusCode::InsufficientStorage,
        ],
        'exception_to_code' => [
            NotFoundHttpException::class => ErrorCode::Code1,
        ],
    ],
]);
