<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;
use EonX\EasyDoctrine\Bundle\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopierInterface;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\EntityEvent\EntityManager\WithEventsEntityManager;
use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ObjectCopierInterface::class)
        ->factory([ObjectCopierFactory::class, 'create']);

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class);

    $services->set(WithEventsEntityManager::class)
        ->arg('$decorated', service('.inner'))
        ->decorate('doctrine.orm.default_entity_manager');

    $services
        ->set(EntityEventListener::class)
        ->arg('$trackableEntities', param(ConfigParam::DeferredDispatcherEntities->value));
};
