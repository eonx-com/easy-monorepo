<?php
declare(strict_types=1);

namespace EonX\EasyActivity\EasyDoctrine\Subscriber;

use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Common\Logger\ActivityLoggerInterface;
use EonX\EasyDoctrine\EntityEvent\Event\EntityCreatedEvent;
use EonX\EasyDoctrine\EntityEvent\Event\EntityDeletedEvent;
use EonX\EasyDoctrine\EntityEvent\Event\EntityUpdatedEvent;

final class EasyDoctrineEntityEventsSubscriber implements EasyDoctrineEntityEventsSubscriberInterface
{
    public function __construct(
        private readonly ActivityLoggerInterface $activityLogger,
        private bool $enabled,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityCreatedEvent::class => ['onCreate'],
            EntityDeletedEvent::class => ['onDelete'],
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
        $this->dispatchLogEntry(ActivityAction::Create, $event->getEntity(), $event->getChangeSet());
    }

    public function onDelete(EntityDeletedEvent $event): void
    {
        $this->dispatchLogEntry(ActivityAction::Delete, $event->getEntity(), $event->getChangeSet());
    }

    public function onUpdate(EntityUpdatedEvent $event): void
    {
        $this->dispatchLogEntry(ActivityAction::Update, $event->getEntity(), $event->getChangeSet());
    }

    private function dispatchLogEntry(ActivityAction $action, object $object, array $changeSet): void
    {
        if ($this->enabled === false) {
            return;
        }

        $this->activityLogger->addActivityLogEntry($action, $object, $changeSet);
    }
}
