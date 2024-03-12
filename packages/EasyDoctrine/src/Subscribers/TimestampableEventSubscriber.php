<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyDoctrine\Listeners\TimestampableEventListener;

/**
 * @deprecated since 5.11, will be removed in 6.0. Use \EonX\EasyDoctrine\Listeners\TimestampableEventListener instead.
 */
final class TimestampableEventSubscriber implements EventSubscriber
{
    private TimestampableEventListener $timestampableEventListener;

    public function __construct()
    {
        $this->timestampableEventListener = new TimestampableEventListener();
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $this->timestampableEventListener->loadClassMetadata($loadClassMetadataEventArgs);
    }
}
