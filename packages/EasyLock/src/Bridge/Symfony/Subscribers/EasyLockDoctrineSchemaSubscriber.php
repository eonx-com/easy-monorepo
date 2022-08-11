<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony\Subscribers;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\DoctrineDbalStore;

final class EasyLockDoctrineSchemaSubscriber implements EventSubscriber
{
    public function __construct(private PersistingStoreInterface $persistingStore)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            ToolEvents::postGenerateSchema,
        ];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        if ($this->persistingStore instanceof DoctrineDbalStore) {
            $this->persistingStore->configureSchema($event->getSchema());
        }
    }
}
