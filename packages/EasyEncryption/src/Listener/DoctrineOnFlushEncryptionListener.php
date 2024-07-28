<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyEncryption\Encryptor\EncryptableEncryptor;
use EonX\EasyEncryption\Interfaces\EncryptableInterface;
use WeakMap;

#[AsDoctrineListener(Events::onFlush)]
// We need to set the priority to 1 to ensure that the decryption is done before any other listener
#[AsDoctrineListener(Events::postFlush, priority: 1)]
final class DoctrineOnFlushEncryptionListener
{
    private WeakMap $weakMap;

    public function __construct(
        private EncryptableEncryptor $encryptableEncryptor,
    ) {
        $this->weakMap = new WeakMap();
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof EncryptableInterface === false) {
                continue;
            }

            $this->encryptData($objectManager, $entity);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof EncryptableInterface === false) {
                continue;
            }

            $this->encryptData($objectManager, $entity);
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();

        foreach ($this->weakMap as $entity) {
            $this->encryptableEncryptor->decrypt($entity);

            // When we decrypt the entity, we have to trick Doctrine into thinking that the entity has not changed
            // We call recomputeSingleEntityChangeSet to turn actual changes into original changes
            // Then we call clearEntityChangeSet to remove the changes from the entity
            // As a result Doctrine does not think that the entity has changed
            $entityMetadata = $objectManager->getClassMetadata($entity::class);
            $unitOfWork->recomputeSingleEntityChangeSet($entityMetadata, $entity);
            $unitOfWork->clearEntityChangeSet(\spl_object_id($entity));
        }

        // We reset the WeakMap to be prepared for a next flush during the same request
        $this->weakMap = new WeakMap();
    }

    private function encryptData(EntityManagerInterface $objectManager, EncryptableInterface $entity): void
    {
        $this->encryptableEncryptor->encrypt($entity);

        // We run this code in onFlush and the change set is already computed
        // So we have to recompute the change set to include the encrypted data
        $entityMetadata = $objectManager->getClassMetadata($entity::class);
        $objectManager->getUnitOfWork()
->recomputeSingleEntityChangeSet($entityMetadata, $entity);

        // We store the entity in a WeakMap to decrypt it in the postFlush method
        // We use WeakMap to ensure that the entity will not be decrypted twice
        $this->weakMap[$entity] = $entity;
    }
}
