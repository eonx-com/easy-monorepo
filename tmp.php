<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyNotification\Bundle\Enum\ConfigServiceId;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ConfigServiceId::ConfigCache->value, PhpFilesAdapter::class)
        ->arg('$namespace', 'eonx_notification_config');
};
