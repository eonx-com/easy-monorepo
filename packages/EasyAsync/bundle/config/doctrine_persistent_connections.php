<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bundle\Enum\BundleParam;
use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use EonX\EasyAsync\Doctrine\Closer\ManagersCloser;
use EonX\EasyAsync\Messenger\Listener\ClosePersistentConnectionListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ManagersCloser::class)
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    $services
        ->set(ClosePersistentConnectionListener::class)
        ->arg('$maxIdleTime', param(ConfigParam::DoctrinePersistentConnectionsMaxIdleTime->value))
        ->tag('kernel.event_listener');
};
