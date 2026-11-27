<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Doctrine\Warmer\ConnectionsWarmer;
use EonX\EasyServerless\Doctrine\Warmer\EntityMetadataWarmer;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(EntityMetadataWarmer::class)
        ->tag(SnapStartTag::Warmer->value, ['priority' => 200]);

    // Closes the connections opened during the initialization, it must run last
    $services
        ->set(ConnectionsWarmer::class)
        ->tag(SnapStartTag::Warmer->value, ['priority' => \PHP_INT_MIN]);
};
