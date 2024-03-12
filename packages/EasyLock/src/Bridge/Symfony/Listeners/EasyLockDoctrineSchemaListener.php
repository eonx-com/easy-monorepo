<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony\Listeners;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\DoctrineDbalStore;

final class EasyLockDoctrineSchemaListener
{
    public function __construct(
        private PersistingStoreInterface $persistingStore,
    ) {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        if ($this->persistingStore instanceof DoctrineDbalStore) {
            $this->persistingStore->configureSchema($event->getSchema());
        }
    }
}
