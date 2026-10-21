<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Controller\ResponseController;
use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener\SlowTerminateListener;
use EonX\EasyTest\Monolog\Processor\LogCollectorProcessor;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(ResponseController::class)
        ->tag('controller.service_arguments');

    $services->set(LogCollectorProcessor::class);

    $services->set(SlowTerminateListener::class);
};
