<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyEncryption\Encryptor\EncryptableEncryptor;
use EonX\EasyEncryption\Interfaces\EncryptableInterface;

#[AsDoctrineListener(Events::postLoad)]
final class DoctrinePostLoadEncryptionListener
{
    public function __construct(private EncryptableEncryptor $encryptableEncryptor)
    {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();
        $entity = $args->getObject();

        if ($entity instanceof EncryptableInterface === false) {
            return;
        }

        $this->encryptableEncryptor->decrypt($entity);

        // When we decrypt the entity, we have to trick Doctrine into thinking that the entity has not changed
        // We call recomputeSingleEntityChangeSet to turn actual changes into original changes
        // Then we call clearEntityChangeSet to remove the changes from the entity
        // As a result Doctrine does not think that the entity has changed
        $entityMetadata = $objectManager->getClassMetadata($entity::class);
        $unitOfWork->recomputeSingleEntityChangeSet($entityMetadata, $entity);
        $unitOfWork->clearEntityChangeSet(\spl_object_id($entity));
    }
}
