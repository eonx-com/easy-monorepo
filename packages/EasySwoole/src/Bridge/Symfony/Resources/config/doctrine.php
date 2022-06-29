<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bridge\Doctrine\Orm\ManagersChecker;
use EonX\EasySwoole\Bridge\Doctrine\Orm\ManagersResetter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(ManagersChecker::class)
        ->set(ManagersResetter::class);
};
