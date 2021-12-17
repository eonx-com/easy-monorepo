<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyPagination\Bridge\Symfony\Factories\StartSizeConfigFactory;
use EonX\EasyPagination\Bridge\Symfony\Factories\StartSizeDataFactory as BridgeStartSizeDataFactory;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Factories\StartSizeDataFactory;
use EonX\EasyPagination\Interfaces\StartSizeConfigInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataFactoryInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Resolvers\Config\StartSizeConfig;
use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use EonX\EasyPagination\Resolvers\StartSizeInQueryResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    # Config
    $services->set(StartSizeConfigFactory::class)
        ->arg('$config', '%easy_pagination.start_size_config%');

    $services->set(StartSizeConfigInterface::class, StartSizeConfig::class)
        ->factory([service(StartSizeConfigFactory::class), '__invoke']);

    # Data
    $services->set(BridgeStartSizeDataFactory::class);

    $services->set(StartSizeDataInterface::class, StartSizeData::class)
        ->factory([service(BridgeStartSizeDataFactory::class), '__invoke']);

    # Data Factory to be used by apps
    $services->set(StartSizeDataFactoryInterface::class, StartSizeDataFactory::class);

    # Resolvers
    $services->set(StartSizeAsArrayInQueryResolver::class)
        ->arg('$queryAttr', '%easy_pagination.array_in_query_attr%');

    $services->set(StartSizeInQueryResolver::class);
};
