<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Pagination\StatelessPagination;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(PaginationInterface::class, StatelessPagination::class);
};
