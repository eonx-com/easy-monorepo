<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Command\ApiResourceAndSimpleDataPersisterMaker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ApiResourceAndSimpleDataPersisterMaker::class);
};
