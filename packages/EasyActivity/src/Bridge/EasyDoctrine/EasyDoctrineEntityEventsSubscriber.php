<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\EasyDoctrine;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\ActivityLoggerInterface;
use EonX\EasyDoctrine\Events\EntityCreatedEvent;
use EonX\EasyDoctrine\Events\EntityUpdatedEvent;

final class EasyDoctrineEntityEventsSubscriber implements EasyDoctrineEntityEventsSubscriberInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActivityLoggerInterface
     */
    private $activityLogger;

    /**
     * @var bool
     */
    private $enabled;

    public function __construct(ActivityLoggerInterface $activityLogger, bool $enabled)
    {
        $this->activityLogger = $activityLogger;
        $this->enabled = $enabled;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityCreatedEvent::class => ['onCreate'],
            EntityUpdatedEvent::class => ['onUpdate'],
        ];
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function onCreate(EntityCreatedEvent $event): void
    {
        $this->dispatchLogEntry(ActivityLogEntry::ACTION_CREATE, $event->getEntity(), $event->getChangeSet());
    }

    public function onUpdate(EntityUpdatedEvent $event): void
    {
        $this->dispatchLogEntry(ActivityLogEntry::ACTION_UPDATE, $event->getEntity(), $event->getChangeSet());
    }

    /**
     * @param array<string, array<string, mixed>> $changeSet
     */
    private function dispatchLogEntry(string $action, object $object, array $changeSet): void
    {
        if ($this->enabled === false) {
            return;
        }

        $this->activityLogger->addActivityLogEntry($action, $object, $changeSet);
    }
}
