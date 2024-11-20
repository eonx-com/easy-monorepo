<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\Enum\ErrorCode;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Config\EasyApiPlatformConfig;

return static function (EasyApiPlatformConfig $easyApiPlatformConfig): void {
    $easyErrorHandlerConfig = $easyApiPlatformConfig->easyErrorHandler();

    $easyErrorHandlerConfig->customSerializerExceptions()
        ->class(UnexpectedValueException::class)
        ->messagePattern('/Custom message from custom CarbonNormalizer./')
        ->violationMessage('violations.invalid_datetime');

    $easyErrorHandlerConfig->validationErrorCode(ErrorCode::ValidationError);
};
