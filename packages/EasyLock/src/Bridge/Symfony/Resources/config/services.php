<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use EonX\EasyLock\Bridge\Symfony\Subscribers\EasyLockDoctrineSchemaSubscriber;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(LockServiceInterface::class, LockService::class)
        ->arg('$store', service(BridgeConstantsInterface::SERVICE_STORE))
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);

    $services
        ->set(EasyLockDoctrineSchemaSubscriber::class)
        ->arg('$persistingStore', service(BridgeConstantsInterface::SERVICE_STORE))
        ->tag('doctrine.event_subscriber');
};
