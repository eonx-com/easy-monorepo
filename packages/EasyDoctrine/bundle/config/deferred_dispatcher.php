<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;
use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(EntityEventListener::class)
        ->arg('$trackableEntities', param(ConfigParam::DeferredDispatcherEntities->value));
};
