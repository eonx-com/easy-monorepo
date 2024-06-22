<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyPagination\Bundle\Enum\ConfigParam;
use EonX\EasyPagination\Provider\PaginationProvider;
use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyPagination\ValueObject\PaginationConfig;
use EonX\EasyPagination\ValueObject\PaginationConfigInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(PaginationConfigInterface::class, PaginationConfig::class)
        ->arg('$pageAttribute', '%' . ConfigParam::PageAttribute->value . '%')
        ->arg('$pageDefault', '%' . ConfigParam::PageDefault->value . '%')
        ->arg('$perPageAttribute', '%' . ConfigParam::PerPageAttribute->value . '%')
        ->arg('$perPageDefault', '%' . ConfigParam::PerPageDefault->value . '%');

    $services->set(PaginationProviderInterface::class, PaginationProvider::class);

    $services
        ->set(PaginationInterface::class)
        ->factory([service(PaginationProviderInterface::class), 'getPagination']);
};
