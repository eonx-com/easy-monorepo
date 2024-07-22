<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bundle\Enum\ConfigParam;
use EonX\EasySwoole\Doctrine\Checker\ManagersChecker;
use EonX\EasySwoole\Doctrine\Initializer\ManagerConnectionsInitializer;
use EonX\EasySwoole\Doctrine\Resetter\ManagersResetter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(ManagerConnectionsInitializer::class)
        ->set(ManagersChecker::class);

    $services
        ->set(ManagersResetter::class)
        ->arg('$resetDbalConnections', param(ConfigParam::ResetDoctrineDbalConnections->value));
};
