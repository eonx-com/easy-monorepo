<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bundle\Enum\ConfigServiceId;
use EonX\EasyDoctrine\Bundle\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\Common\Listener\TimestampableListener;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopierInterface;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TimestampableListener::class);

    $services
        ->set(ObjectCopierInterface::class)
        ->deprecate(
            'eonx_com/easy-doctrine',
            '6.0.3',
            '"%service_id%" service is deprecated and will be removed in 7.0.'
            . ' Use ' . ConfigServiceId::DeletedEntityCopier->value . ' instead or register your own service.'
        )
        ->factory([ObjectCopierFactory::class, 'create']);

    $services
        ->set(ConfigServiceId::DeletedEntityCopier->value, ObjectCopierInterface::class)
        ->factory([ObjectCopierFactory::class, 'createForDeletedEntity']);

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class)
        ->arg('$deletedEntityCopier', service(ConfigServiceId::DeletedEntityCopier->value));
};
