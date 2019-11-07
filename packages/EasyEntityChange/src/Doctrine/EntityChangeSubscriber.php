<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyEntityChange\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface;
use EoneoPay\Utils\Arr;
use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\EasyEntityChange\Events\EntityDeleteDataEvent;
use LoyaltyCorp\EasyEntityChange\Exceptions\InvalidDispatcherException;

final class EntityChangeSubscriber implements EventSubscriber
{
    /**
     * @var \EoneoPay\Utils\Arr
     */
    private $arr;

    /**
     * Stores a multi dimensional array of entity class name with all
     * ids of that class that were deleted.
     *
     * @var object[]
     */
    private $deletes = [];

    /**
     * @var \EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Stores a multi dimensional array of entity class name with all
     * ids of that class that were created or updated.
     *
     * @var object[][]
     */
    private $updates = [];

    /**
     * Constructor.
     *
     * @param \EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->arr = new Arr();
        $this->dispatcher = $dispatcher;
    }

    /**
     * Subscribed Events.
     *
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::postFlush
        ];
    }

    /**
     * Takes all entities that are updated or modified in a flush and
     * prepares them for a search index update.
     *
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     *
     * @return void
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->flagForUpdate($entity, $entityManager);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->flagForUpdate($entity, $entityManager);
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $this->flagForDelete(clone $entity);
        }

        foreach ($unitOfWork->getScheduledCollectionUpdates() as $collection) {
            $this->flagCollectionForUpdate($collection, $entityManager);
        }

        foreach ($unitOfWork->getScheduledCollectionDeletions() as $collection) {
            $this->flagCollectionForDelete($collection, $entityManager);
        }
    }

    /**
     * Dispatches jobs for search index updates.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyEntityChange\Exceptions\InvalidDispatcherException
     */
    public function postFlush(): void
    {
        $deletes = $this->deletes;
        $this->deletes = [];

        $updates = $this->updates;
        $this->updates = [];

        // synchronously dispatch to add data to the deletes so that
        // workers have all required information
        $processedDeletes = $this->dispatcher->dispatch(new EntityDeleteDataEvent(
            $deletes
        ));

        if ($processedDeletes === null) {
            // While the DispatcherInterface allows for a null return, we're not calling
            // it with halt = true, and never expect to see a null return - especially
            // since EntityDeleteDataEvent is synchronous.

            throw new InvalidDispatcherException('exceptions.services.entitychange.doctrine.invalid_dispatcher');
        }

        // asynchronously dispatch change events for handling by any
        // interested workers
        $this->dispatcher->dispatch(new EntityChangeEvent(
            \count($processedDeletes) > 0 ? \array_merge(...$processedDeletes) : [],
            $updates
        ));
    }

    /**
     * Flags anything relating to a collection delete as needing
     * a search index update or delete.
     *
     * @param mixed $collection
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return void
     */
    private function flagCollectionForDelete($collection, EntityManagerInterface $entityManager): void
    {
        if (($collection instanceof PersistentCollection) === false) {
            return;
        }

        /**
         * @var \Doctrine\ORM\PersistentCollection $collection
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises === check
         */
        if (($collection->getMapping()['orphanRemoval'] ?? false) === false) {
            // @codeCoverageIgnoreStart
            // the collection does not specify orphanRemoval, the clear() method
            // wont actually do anything.

            return;
            // @codeCoverageIgnoreEnd
        }

        // We're flagging the owner of the collection for updates to allow anything
        // listening to be able to react to changes to any toMany collection on
        // the owner.
        $this->flagForUpdate($collection->getOwner(), $entityManager);

        foreach ($collection->getIterator() as $entity) {
            $this->flagForDelete($entity);
        }
    }

    /**
     * Flags anything relating to a collection update as needing
     * a search index update.
     *
     * @param mixed $collection
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return void
     */
    private function flagCollectionForUpdate($collection, EntityManagerInterface $entityManager): void
    {
        if (($collection instanceof PersistentCollection) === false) {
            return;
        }

        /**
         * @var \Doctrine\ORM\PersistentCollection $collection
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises === check
         */
        $this->flagForUpdate($collection->getOwner(), $entityManager);

        foreach ($collection->getIterator() as $entity) {
            $this->flagForUpdate($entity, $entityManager);
        }
    }

    /**
     * Collects actual deleted entities so they can be processed into relevant information
     * for queue workers to handle.
     *
     * @param mixed $entity
     *
     * @return void
     */
    private function flagForDelete($entity): void
    {
        $this->deletes[] = $entity;
    }

    /**
     * Flags the entity for update if the search manager indicates that
     * it is searchable.
     *
     * @param object $entity
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return void
     */
    private function flagForUpdate(object $entity, EntityManagerInterface $entityManager): void
    {
        $meta = $entityManager->getClassMetadata(\get_class($entity));
        if ($meta->isIdentifierComposite) {
            // @codeCoverageIgnoreStart
            // we do not support composite identifiers for elasticsearch
            return;
            // @cardCoverageIgnoreEnd
        }

        $entityIds = $meta->getIdentifierValues($entity);
        $entityId = \reset($entityIds);
        if ($entityId === false) {
            // @codeCoverageIgnoreStart
            // Entities will always have an id at this point
            return;
            // @codeCoverageIgnoreEnd
        }

        $key = $this->getArrayPath($entity);

        $this->arr->set($this->updates, $key, $entityId);
    }

    /**
     * Returns a unique array path for the provided entity.
     *
     * @param object $entity
     *
     * @return string
     */
    private function getArrayPath(object $entity): string
    {
        return \sprintf('%s.%s', \get_class($entity), \spl_object_hash($entity));
    }
}
