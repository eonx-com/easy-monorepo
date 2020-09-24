<?php

declare(strict_types=1);

use EonX\EasyErrorHandler\Reporters\DefaultReporterProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DefaultReporterProvider::class);
};
