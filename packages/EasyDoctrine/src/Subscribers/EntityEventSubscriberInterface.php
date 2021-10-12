<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

interface EntityEventSubscriberInterface extends EventSubscriber
{
    public function addAcceptableEntity(string $acceptableEntityClass): void;

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array;

    public function onFlush(OnFlushEventArgs $eventArgs): void;

    public function postFlush(PostFlushEventArgs $eventArgs): void;
}
