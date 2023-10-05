<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformCustomSerializerExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformMissingConstructorArgumentsExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformNotNormalizableValueExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformTypeErrorExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformUnexpectedValueExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformValidationExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Bridge\Symfony\Provider\ApiPlatformErrorResponseBuilderProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ApiPlatformValidationExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(BridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->tag(BridgeConstantsInterface::TAG_API_PLATFORM_RESPONSE_BUILDER, ['priority' => 99]);

    $services->set(ApiPlatformNotNormalizableValueExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(BridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->tag(BridgeConstantsInterface::TAG_API_PLATFORM_RESPONSE_BUILDER, ['priority' => 98]);

    $services->set(ApiPlatformMissingConstructorArgumentsExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(BridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->tag(BridgeConstantsInterface::TAG_API_PLATFORM_RESPONSE_BUILDER, ['priority' => 97]);

    $services->set(ApiPlatformUnexpectedValueExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(BridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->tag(BridgeConstantsInterface::TAG_API_PLATFORM_RESPONSE_BUILDER, ['priority' => 96]);

    $services->set(ApiPlatformTypeErrorExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(BridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->tag(BridgeConstantsInterface::TAG_API_PLATFORM_RESPONSE_BUILDER, ['priority' => 95]);

    $services->set(ApiPlatformCustomSerializerExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(BridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->arg(
            '$apiPlatformCustomSerializerExceptions',
            param(BridgeConstantsInterface::PARAM_API_PLATFORM_CUSTOM_SERIALIZER_EXCEPTIONS)
        )
        ->tag(BridgeConstantsInterface::TAG_API_PLATFORM_RESPONSE_BUILDER, ['priority' => 94]);

    $services->set(ApiPlatformErrorResponseBuilderProvider::class)
        ->arg('$builders', tagged_iterator(BridgeConstantsInterface::TAG_API_PLATFORM_RESPONSE_BUILDER));
};
