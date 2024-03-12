<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use EonX\EasyDoctrine\Listeners\TimestampableEventListener;

/**
 * @deprecated since 5.11, will be removed in 6.0. Use \EonX\EasyDoctrine\Listeners\TimestampableEventListener instead.
 */
final class TimestampableEventSubscriber extends TimestampableEventListener implements EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }
}
