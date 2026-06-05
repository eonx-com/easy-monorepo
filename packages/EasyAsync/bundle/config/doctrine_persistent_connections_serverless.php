<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use EonX\EasyAsync\Messenger\Subscriber\ClosePersistentConnectionServerlessSubscriber;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ClosePersistentConnectionServerlessSubscriber::class)
        ->arg('$maxIdleTime', param(ConfigParam::DoctrineClosePersistentConnectionsMaxIdleTime->value));
};
