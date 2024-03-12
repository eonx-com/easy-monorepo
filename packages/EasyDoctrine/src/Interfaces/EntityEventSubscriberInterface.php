<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Interfaces;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * @deprecated since 5.11, will be removed in 6.0. Use \EonX\EasyDoctrine\Listeners\EntityEventListener instead.
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
