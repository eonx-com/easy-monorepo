<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\EasyBugsnag\Configurator\SwooleWorkerIdClientConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(SwooleWorkerIdClientConfigurator::class);
};
