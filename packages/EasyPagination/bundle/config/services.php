<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyPagination\Bundle\Enum\ConfigParam;
use EonX\EasyPagination\Provider\PaginationConfigProvider;
use EonX\EasyPagination\Provider\PaginationConfigProviderInterface;
use EonX\EasyPagination\Provider\PaginationProvider;
use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(PaginationConfigProviderInterface::class, PaginationConfigProvider::class)
        ->arg('$pageAttribute', param(ConfigParam::PageAttribute->value))
        ->arg('$pageDefault', param(ConfigParam::PageDefault->value))
        ->arg('$perPageAttribute', param(ConfigParam::PerPageAttribute->value))
        ->arg('$perPageDefault', param(ConfigParam::PerPageDefault->value));

    $services->set(PaginationProviderInterface::class, PaginationProvider::class);

    $services
        ->set(PaginationInterface::class)
        ->factory([service(PaginationProviderInterface::class), 'getPagination']);
};
