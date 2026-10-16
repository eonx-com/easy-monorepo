<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Bundle\Enum\BundleParam;
use EonX\EasyLock\Bundle\Enum\ConfigParam;
use EonX\EasyLock\Messenger\Middleware\ProcessWithLockMiddleware;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ProcessWithLockMiddleware::class)
        ->arg('$lockNotAcquiredLogLevel', param(ConfigParam::MessengerMiddlewareLockNotAcquiredLogLevel->value))
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);
};
