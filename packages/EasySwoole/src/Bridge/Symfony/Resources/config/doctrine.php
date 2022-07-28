<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Bridge\Doctrine\Orm\ManagerConnectionsInitializer;
use EonX\EasySwoole\Bridge\Doctrine\Orm\ManagersChecker;
use EonX\EasySwoole\Bridge\Doctrine\Orm\ManagersResetter;

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
        ->arg('$resetDbalConnections', param(BridgeConstantsInterface::PARAM_RESET_DOCTRINE_DBAL_CONNECTIONS));
};
