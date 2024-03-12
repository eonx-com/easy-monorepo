<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Interfaces\EntityEventSubscriberInterface;
use EonX\EasyDoctrine\Listeners\EntityEventListener;
use InvalidArgumentException;

/**
 * @deprecated since 5.11, will be removed in 6.0. Use \EonX\EasyDoctrine\Listeners\EntityEventListener instead.
 */
final class EntityEventSubscriber implements EntityEventSubscriberInterface
{
    private EntityEventListener $entityEventListener;

    /**
     * @param class-string[]|null $entities
     * @param class-string[]|null $subscribedEntities
     */
    public function __construct(
        DeferredEntityEventDispatcherInterface $eventDispatcher,
        // @deprecated Since 4.5, will be removed in 6.0. Use $subscribedEntities instead
        ?array $entities = null,
        ?array $subscribedEntities = null,
    ) {
        $this->entityEventListener = new EntityEventListener(
            $eventDispatcher,
            $entities
                ?? $subscribedEntities
                ?? throw new InvalidArgumentException('You must provide at least one entity to subscribe to')
        );
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $this->entityEventListener->onFlush($eventArgs);
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $this->entityEventListener->postFlush($eventArgs);
    }
}
