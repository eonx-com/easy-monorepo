<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Bundle\Enum\ConfigParam;
use EonX\EasyApiPlatform\Bundle\Enum\ConfigTag;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\AbstractApiPlatformErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\CustomSerializerExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\NotEncodableValueExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ValidationExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Provider\ApiPlatformErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam as EasyErrorHandlerConfigParam;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag as EasyErrorHandlerConfigTag;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(AbstractApiPlatformErrorResponseBuilder::class)
        ->abstract()
        ->arg('$keys', param(EasyErrorHandlerConfigParam::ResponseKeys->value))
        ->arg('$nameConverter', service('api_platform.name_converter')->ignoreOnInvalid())
        ->arg('$validationErrorCode', param(ConfigParam::EasyErrorHandlerValidationErrorCode->value));

    $services->set(ValidationExceptionErrorResponseBuilder::class)
        ->parent(AbstractApiPlatformErrorResponseBuilder::class)
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 90]);

    $services->set(CustomSerializerExceptionErrorResponseBuilder::class)
        ->parent(AbstractApiPlatformErrorResponseBuilder::class)
        ->call(
            'setCustomSerializerExceptions',
            [
                param(ConfigParam::EasyErrorHandlerCustomSerializerExceptions->value),
            ]
        )
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 80]);

    $services->set(NotEncodableValueExceptionErrorResponseBuilder::class)
        ->parent(AbstractApiPlatformErrorResponseBuilder::class)
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 70]);

    $services->set(ApiPlatformErrorResponseBuilderProvider::class)
        ->arg('$builders', tagged_iterator(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value))
        ->tag(EasyErrorHandlerConfigTag::ErrorResponseBuilderProvider->value, ['priority' => -1]);
};
