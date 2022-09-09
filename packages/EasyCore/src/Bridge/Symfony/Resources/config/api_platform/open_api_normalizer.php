<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\BridgeConstantsInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\OpenApiNormalizerDecorator;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor\SortDocsPathsByTagsProcessor;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor\SortDocsQueryParametersProcessor;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor\UpdateDocsComponentsSchemasProcessor;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor\UpdateDocsPathsProcessor;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Provider\OpenApiContextProvider;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Provider\OpenApiProcessorsProvider;
use Symfony\Component\Validator\Validator\ValidatorInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(UpdateDocsComponentsSchemasProcessor::class)
        ->arg('$openApiContextProvider', service(OpenApiContextProvider::class))
        ->tag('open_api_normalizer.processor', ['priority' => -10]);

    $services->set(UpdateDocsPathsProcessor::class)
        ->arg('$openApiContextProvider', service(OpenApiContextProvider::class))
        ->arg(
            '$endpointsRemoveParams',
            '%' . BridgeConstantsInterface::PARAM_OPEN_API_NORMALIZER_DOC_PATH_PROCESSOR_ENDPOINTS_REMOVE_PARAMS . '%'
        )
        ->arg(
            '$endpointsRemoveBody',
            '%' . BridgeConstantsInterface::PARAM_OPEN_API_NORMALIZER_DOC_PATH_PROCESSOR_ENDPOINTS_REMOVE_BODY . '%'
        )
        ->arg(
            '$endpointsRemoveResponse',
            '%' . BridgeConstantsInterface::PARAM_OPEN_API_NORMALIZER_DOC_PATH_PROCESSOR_ENDPOINTS_REMOVE_RESPONSE . '%'
        )
        ->arg(
            '$skipMethodNames',
            '%' . BridgeConstantsInterface::PARAM_OPEN_API_NORMALIZER_DOC_PATH_PROCESSOR_SKIP_METHOD_NAMES . '%'
        )
        ->tag('open_api_normalizer.processor', ['priority' => -20]);

    $services->set(SortDocsPathsByTagsProcessor::class)
        ->tag('open_api_normalizer.processor', ['priority' => -30]);

    $services->set(SortDocsQueryParametersProcessor::class)
        ->tag('open_api_normalizer.processor', ['priority' => -40]);

    $services->set(OpenApiProcessorsProvider::class)
        ->arg(
            '$defaultProcessorsEnabled',
            param(BridgeConstantsInterface::PARAM_OPEN_API_NORMALIZER_DEFAULT_PROCESSORS_ENABLED)
        )
        ->arg('$processors', tagged_iterator(tag: 'open_api_normalizer.processor'))
        ->autoconfigure(false);

    $services->set(OpenApiNormalizerDecorator::class)
        ->decorate('api_platform.openapi.normalizer')
        ->arg('$processorsProvider', service(OpenApiProcessorsProvider::class))
        ->arg('$decorated', service('.inner'))
        ->arg('$baseApiUri', '%' . BridgeConstantsInterface::PARAM_OPEN_API_NORMALIZER_BASE_URI . '%')
        ->arg('$environment', param('kernel.environment'))
        ->autoconfigure(false);

    $services->set(OpenApiContextProvider::class)
        ->arg('$openApiContextsFile', '%' . BridgeConstantsInterface::PARAM_OPEN_API_NORMALIZER_CONTEXTS_FILE . '%')
        ->arg('$validator', service(ValidatorInterface::class));
};
