<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(LockServiceInterface::class, LockService::class)
        ->args([ref(BridgeConstantsInterface::SERVICE_STORE), ref(LoggerInterface::class)])
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);
};
