<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\EasyDoctrine;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\AsyncDispatcherInterface;
use EonX\EasyDoctrine\Events\EntityCreatedEvent;
use EonX\EasyDoctrine\Events\EntityUpdatedEvent;

final class EasyDoctrineEntityEventsSubscriber implements EasyDoctrineEntityEventsSubscriberInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface
     */
    private $activityLogEntryFactory;

    /**
     * @var \EonX\EasyActivity\Interfaces\AsyncDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $enabled;

    public function __construct(
        AsyncDispatcherInterface $dispatcher,
        ActivityLogEntryFactoryInterface $activityLogEntryFactory,
        bool $enabled
    ) {
        $this->dispatcher = $dispatcher;
        $this->activityLogEntryFactory = $activityLogEntryFactory;
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
     * @param string $action
     * @param object $entity
     * @param array<string, array<string, mixed>> $changeSet
     */
    private function dispatchLogEntry(string $action, object $entity, array $changeSet): void
    {
        if ($this->enabled === false) {
            return;
        }

        $oldData = [];
        $data = [];
        foreach ($changeSet as $field => [$oldValue, $newValue]) {
            $data[$field] = $newValue;
            $oldData[$field] = $oldValue;
        }

        if ($action === ActivityLogEntry::ACTION_CREATE) {
            $oldData = null;
        }

        if ($action === ActivityLogEntry::ACTION_DELETE) {
            $data = null;
        }

        $logEntry = $this->activityLogEntryFactory->create($action, $entity, $data, $oldData);

        if ($logEntry !== null) {
            $this->dispatcher->dispatch($logEntry);
        }
    }
}
