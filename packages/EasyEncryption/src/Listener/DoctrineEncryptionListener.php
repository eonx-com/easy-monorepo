<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyEncryption\Encryptor\EncryptableEncryptor;
use EonX\EasyEncryption\Interfaces\EncryptableInterface;
use WeakMap;

#[AsDoctrineListener(Events::onFlush)]
// We need to set the priority to 1 to ensure that the decryption is done before any other listener
#[AsDoctrineListener(Events::postFlush, priority: 1)]
#[AsDoctrineListener(Events::postLoad)]
final class DoctrineEncryptionListener
{
    /**
     * @var \WeakMap<EncryptableInterface, EncryptableInterface>
     */
    private WeakMap $weakMap;

    public function __construct(private EncryptableEncryptor $encryptableEncryptor)
    {
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

            $this->encryptEntity($objectManager, $entity);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof EncryptableInterface === false) {
                continue;
            }

            $this->encryptEntity($objectManager, $entity);
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();

        foreach ($this->weakMap as $entity) {
            $this->decryptEntity($objectManager, $entity);
        }

        // We reset the WeakMap to be prepared for a next flush during the same request
        $this->weakMap = new WeakMap();
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $entity = $args->getObject();

        if ($entity instanceof EncryptableInterface === false) {
            return;
        }

        $this->decryptEntity($objectManager, $entity);
    }

    private function decryptEntity(EntityManagerInterface $objectManager, EncryptableInterface $entity): void
    {
        $this->encryptableEncryptor->decrypt($entity);

        // When we decrypt the entity, we have to trick Doctrine into thinking that the entity has not changed
        $entityMetadata = $objectManager->getClassMetadata($entity::class);
        $unitOfWork = $objectManager->getUnitOfWork();

        // We call "propertyChanged" to create "entityChangeSets" for the entity in the UnitOfWork. It's important for
        // the next step
        $unitOfWork->propertyChanged($entity, 'id', null, null);
        // Then we call "recomputeSingleEntityChangeSet" to turn actual changes into original changes. Because of
        // the previous step this call does not create "entityUpdates" for the entity in the UnitOfWork
        $unitOfWork->recomputeSingleEntityChangeSet($entityMetadata, $entity);
        // Then we call "clearEntityChangeSet" to remove any changes from the entity
        $unitOfWork->clearEntityChangeSet(\spl_object_id($entity));
        // As a result Doctrine does not think that the entity has changed
    }

    private function encryptEntity(EntityManagerInterface $objectManager, EncryptableInterface $entity): void
    {
        $this->encryptableEncryptor->encrypt($entity);

        // We run this code in onFlush and the change set is already computed
        // So we have to recompute the change set to include the encrypted data
        $entityMetadata = $objectManager->getClassMetadata($entity::class);
        $objectManager->getUnitOfWork()->recomputeSingleEntityChangeSet($entityMetadata, $entity);

        // We store the entity in a WeakMap to decrypt it in the postFlush method
        // We use WeakMap to ensure that the entity will not be decrypted twice
        $this->weakMap[$entity] = $entity;
    }
}
