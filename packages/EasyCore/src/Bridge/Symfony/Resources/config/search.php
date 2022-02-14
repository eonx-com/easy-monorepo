<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\BridgeConstantsInterface;
use EonX\EasyCore\Search\ElasticsearchSearchService;
use EonX\EasyCore\Search\ElasticsearchSearchServiceFactory;
use EonX\EasyCore\Search\SearchServiceFactoryInterface;
use EonX\EasyCore\Search\SearchServiceInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(SearchServiceFactoryInterface::class, ElasticsearchSearchServiceFactory::class)
        ->arg('$elasticsearchHost', '%' . BridgeConstantsInterface::PARAM_ELASTICSEARCH_HOST . '%');

    $services
        ->set(SearchServiceInterface::class, ElasticsearchSearchService::class)
        ->factory([service(SearchServiceFactoryInterface::class), 'create'])
        ->public();
};
