<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Listeners;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;

#[AsDoctrineListener(event: Events::postFlush)]
final class EntityPostFlushEventListener
{
    public function __construct(
        private readonly DeferredEntityEventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $objectManager = $eventArgs->getObjectManager();

        if ($objectManager->getConnection()->getTransactionNestingLevel() === 0) {
            $this->eventDispatcher->dispatch();
        }
    }
}
