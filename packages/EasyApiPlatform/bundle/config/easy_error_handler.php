<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Bundle\Enum\ConfigParam;
use EonX\EasyApiPlatform\Bundle\Enum\ConfigTag;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\AbstractApiPlatformExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ApiPlatformCustomSerializerExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ApiPlatformMissingConstructorArgumentsExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ApiPlatformNotEncodableValueExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ApiPlatformNotNormalizableValueExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ApiPlatformTypeErrorExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ApiPlatformUnexpectedValueExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ApiPlatformValidationExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\EasyErrorHandler\Provider\ApiPlatformErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam as EasyErrorHandlerConfigParam;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag as EasyErrorHandlerConfigTag;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // @todo Update priority with step 10 in 7.0 to allow adding more builders in the middle
    $services->set(AbstractApiPlatformExceptionErrorResponseBuilder::class)
        ->abstract()
        ->arg('$keys', param(EasyErrorHandlerConfigParam::ResponseKeys->value))
        // @todo Replace $nameConverter with service('api_platform.name_converter')->ignoreOnInvalid() in 7.0
        ->arg('$nameConverter', service('serializer.name_converter.metadata_aware'))
        ->arg('$validationErrorCode', param(ConfigParam::EasyErrorHandlerValidationErrorCode->value));

    $services->set(ApiPlatformValidationExceptionErrorResponseBuilder::class)
        ->parent(AbstractApiPlatformExceptionErrorResponseBuilder::class)
        ->call('setIriConverter', [service('api_platform.symfony.iri_converter')])
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 99]);

    $services->set(ApiPlatformNotNormalizableValueExceptionErrorResponseBuilder::class)
        ->deprecate('eonx_com/easy-api-platform', '6.4.0', 'The %service_id% will be removed in 7.0.')
        ->parent(AbstractApiPlatformExceptionErrorResponseBuilder::class)
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 98]);

    $services->set(ApiPlatformMissingConstructorArgumentsExceptionErrorResponseBuilder::class)
        ->deprecate('eonx_com/easy-api-platform', '6.4.0', 'The %service_id% will be removed in 7.0.')
        ->parent(AbstractApiPlatformExceptionErrorResponseBuilder::class)
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 97]);

    $services->set(ApiPlatformUnexpectedValueExceptionErrorResponseBuilder::class)
        ->deprecate('eonx_com/easy-api-platform', '6.4.0', 'The %service_id% will be removed in 7.0.')
        ->parent(AbstractApiPlatformExceptionErrorResponseBuilder::class)
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 96]);

    $services->set(ApiPlatformTypeErrorExceptionErrorResponseBuilder::class)
        ->parent(AbstractApiPlatformExceptionErrorResponseBuilder::class)
        ->call('setIriConverter', [service('api_platform.symfony.iri_converter')])
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 95]);

    $services->set(ApiPlatformCustomSerializerExceptionErrorResponseBuilder::class)
        ->parent(AbstractApiPlatformExceptionErrorResponseBuilder::class)
        ->call(
            'setCustomSerializerExceptions',
            [
                param(ConfigParam::EasyErrorHandlerCustomSerializerExceptions->value),
            ]
        )
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 94]);

    $services->set(ApiPlatformNotEncodableValueExceptionErrorResponseBuilder::class)
        ->parent(AbstractApiPlatformExceptionErrorResponseBuilder::class)
        ->tag(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value, ['priority' => 93]);

    $services->set(ApiPlatformErrorResponseBuilderProvider::class)
        ->arg('$builders', tagged_iterator(ConfigTag::EasyErrorHandlerErrorResponseBuilder->value))
        ->tag(EasyErrorHandlerConfigTag::ErrorResponseBuilderProvider->value, ['priority' => -1]);
};
