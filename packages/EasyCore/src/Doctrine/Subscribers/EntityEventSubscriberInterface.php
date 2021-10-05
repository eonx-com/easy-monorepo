<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Subscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * @deprecated since 3.5, will be removed in 4.0. Use EasyDoctrine instead.
 */
interface EntityEventSubscriberInterface extends EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array;

    public function onFlush(OnFlushEventArgs $eventArgs): void;

    public function postFlush(PostFlushEventArgs $eventArgs): void;
}
