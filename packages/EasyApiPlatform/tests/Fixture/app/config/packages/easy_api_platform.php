<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\Enum\ErrorCode;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

return App::config([
    'easy_api_platform' => [
        'easy_error_handler' => [
            'custom_serializer_exceptions' => [
                [
                    'class' => UnexpectedValueException::class,
                    'message_pattern' => '/Custom message from custom CarbonNormalizer./',
                    'violation_message' => 'violations.invalid_datetime',
                ],
            ],
            'validation_error_code' => ErrorCode::ValidationError,
        ],
    ],
]);
