<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bref\Symfony\Messenger\Service\BusDriver;
use EonX\EasyServerless\EasyErrorHandler\BusDriver\ReportBusDriver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ReportBusDriver::class)
        ->decorate(BusDriver::class, priority: -1000);
};
