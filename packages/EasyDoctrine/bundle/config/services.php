<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DeepCopy\DeepCopy;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\Collections\Collection;
use EonX\EasyDoctrine\Bundle\Enum\ConfigServiceId;
use EonX\EasyDoctrine\Common\Listener\TimestampableListener;
use EonX\EasyDoctrine\DeepCopy\Filter\DoctrineInitializedCollectionDeepCopyFilter;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopier;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TimestampableListener::class);

    $services
        ->set(ConfigServiceId::DeletedEntityCopier->value, ObjectCopier::class)
        ->arg(
            '$deepCopy',
            inline_service(DeepCopy::class)
                ->call('addFilter', [
                    inline_service(DoctrineInitializedCollectionDeepCopyFilter::class),
                    inline_service(PropertyTypeMatcher::class)
                        ->args([Collection::class]),
                ])
        );

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class)
        ->arg('$deletedEntityCopier', service(ConfigServiceId::DeletedEntityCopier->value));
};
