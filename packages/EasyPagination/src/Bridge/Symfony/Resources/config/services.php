<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyPagination\Bridge\BridgeConstantsInterface;
use EonX\EasyPagination\Interfaces\PaginationConfigInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Interfaces\PaginationProviderInterface;
use EonX\EasyPagination\PaginationConfig;
use EonX\EasyPagination\PaginationProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(PaginationConfigInterface::class, PaginationConfig::class)
        ->arg('$pageAttribute', '%' . BridgeConstantsInterface::PARAM_PAGE_ATTRIBUTE . '%')
        ->arg('$pageDefault', '%' . BridgeConstantsInterface::PARAM_PAGE_DEFAULT . '%')
        ->arg('$perPageAttribute', '%' . BridgeConstantsInterface::PARAM_PER_PAGE_ATTRIBUTE . '%')
        ->arg('$perPageDefault', '%' . BridgeConstantsInterface::PARAM_PER_PAGE_DEFAULT . '%');

    $services->set(PaginationProviderInterface::class, PaginationProvider::class);

    $services
        ->set(PaginationInterface::class)
        ->factory([service(PaginationProviderInterface::class), 'getPagination']);
};
