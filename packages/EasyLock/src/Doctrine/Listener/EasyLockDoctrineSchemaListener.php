<?php
declare(strict_types=1);

namespace EonX\EasyLock\Doctrine\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\DoctrineDbalStore;

#[AsDoctrineListener(event: ToolEvents::postGenerateSchema)]
final readonly class EasyLockDoctrineSchemaListener
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
