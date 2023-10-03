<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\ClosePersistentConnectionListener;
use EonX\EasyAsync\Doctrine\ManagersCloser;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ManagersCloser::class)
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);

    $services
        ->set(ClosePersistentConnectionListener::class)
        ->arg('$maxIdleTime', param(BridgeConstantsInterface::PARAM_DOCTRINE_PERSISTENT_CONNECTIONS_MAX_IDLE_TIME))
        ->tag('kernel.event_listener');
};
