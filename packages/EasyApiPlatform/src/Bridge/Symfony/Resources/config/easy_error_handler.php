<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Bridge\BridgeConstantsInterface;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Builders\ApiPlatformCustomSerializerExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Builders\ApiPlatformMissingConstructorArgumentsExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Builders\ApiPlatformNotNormalizableValueExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Builders\ApiPlatformTypeErrorExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Builders\ApiPlatformUnexpectedValueExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Builders\ApiPlatformValidationExceptionErrorResponseBuilder;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Interface\ApiPlatformErrorResponseBuilderInterface;
use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Providers\ApiPlatformErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface as EasyErrorHandlerBridgeConstantsInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ApiPlatformValidationExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(EasyErrorHandlerBridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->arg('$priority', 1);

    $services->set(ApiPlatformNotNormalizableValueExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(EasyErrorHandlerBridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->arg('$priority', 2);

    $services->set(ApiPlatformMissingConstructorArgumentsExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(EasyErrorHandlerBridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->arg('$priority', 3);

    $services->set(ApiPlatformUnexpectedValueExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(EasyErrorHandlerBridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->arg('$priority', 4);

    $services->set(ApiPlatformTypeErrorExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(EasyErrorHandlerBridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->arg('$priority', 5);

    $services->set(ApiPlatformCustomSerializerExceptionErrorResponseBuilder::class)
        ->arg('$keys', param(EasyErrorHandlerBridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->arg(
            '$apiPlatformCustomSerializerExceptions',
            param(BridgeConstantsInterface::PARAM_EASY_ERROR_HANDLER_CUSTOM_SERIALIZER_EXCEPTIONS)
        )
        ->arg('$priority', 6);

    $containerBuilder->registerForAutoconfiguration(ApiPlatformErrorResponseBuilderInterface::class)
        ->addTag(BridgeConstantsInterface::TAG_EASY_ERROR_HANDLER_RESPONSE_BUILDER);

    $services->set(ApiPlatformErrorResponseBuilderProvider::class)
        ->arg('$builders', tagged_iterator(BridgeConstantsInterface::TAG_EASY_ERROR_HANDLER_RESPONSE_BUILDER));
};
