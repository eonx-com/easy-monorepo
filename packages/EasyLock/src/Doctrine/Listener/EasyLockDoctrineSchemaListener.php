<?php
declare(strict_types=1);

namespace EonX\EasyLock\Doctrine\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Symfony\Bridge\Doctrine\SchemaListener\AbstractSchemaListener;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\DoctrineDbalStore;

#[AsDoctrineListener(event: ToolEvents::postGenerateSchema)]
final class EasyLockDoctrineSchemaListener extends AbstractSchemaListener
{
    public function __construct(
        private readonly PersistingStoreInterface $persistingStore,
    ) {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        if ($this->persistingStore instanceof DoctrineDbalStore) {
            $connection = $event->getEntityManager()
                ->getConnection();

            $this->persistingStore->configureSchema($event->getSchema(), $this->getIsSameDatabaseChecker($connection));
        }
    }
}
