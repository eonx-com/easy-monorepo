<?php

declare(strict_types=1);

use EonX\EasyPsr7Factory\EasyPsr7Factory;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(EasyPsr7FactoryInterface::class, EasyPsr7Factory::class);
};
