<?php

declare(strict_types=1);

use EonX\EasyBugsnag\Configurators\AwsEcsFargateConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(AwsEcsFargateConfigurator::class);
};
