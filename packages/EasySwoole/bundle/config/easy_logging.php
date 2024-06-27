<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\EasyLogging\Processor\SwooleWorkerIdProcessor;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(SwooleWorkerIdProcessor::class);
};
