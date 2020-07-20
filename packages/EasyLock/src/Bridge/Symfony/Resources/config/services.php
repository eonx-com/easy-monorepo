<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\StoreFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(BridgeConstantsInterface::SERVICE_STORE, PersistingStoreInterface::class)
        ->factory([StoreFactory::class, 'createStore'])
        ->args([ref(BridgeConstantsInterface::SERVICE_CONNECTION)]);

    $services
        ->set(LockServiceInterface::class, LockService::class)
        ->args([
            ref(BridgeConstantsInterface::SERVICE_STORE),
            ref(LoggerInterface::class),
        ]);
};
