<?php

declare(strict_types=1);

namespace EonX\EasyEntityChange\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use EonX\EasyEntityChange\DataTransferObjects\DeletedEntity;
use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use EonX\EasyEntityChange\Events\EntityChangeEvent;
use EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EntityChangeSubscriber implements EventSubscriber
{
    /**
     * Stores an array of ChangedEntity DTOs that were collected during the phases before flush,
     * where we then dispatch an EntityChangeEvent with those DTOs after the flush is successful.
     *
     * @var \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[]
     */
    private $changes = [];

    /**
     * @var \EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface|null
     */
    private $deleteEnrichment;

    /**
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Stores an array of entities that are to be inserted that may not yet have primary ids.
     *
     * @var mixed[]
     */
    private $inserts = [];

    /**
     * Stores an array of class names we're watching for updates. If null, we will watch for
     * any changes, if an array, we will only dispatch when we see a change of the given classes.
     *
     * @var null|string[]
     */
    private $watchedClasses;

    /**
     * @param null|string[] $watchedClasses
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ?DeletedEntityEnrichmentInterface $deleteEnrichment = null,
        ?array $watchedClasses = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->deleteEnrichment = $deleteEnrichment;
        $this->watchedClasses = $watchedClasses;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::onFlush, Events::postFlush];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        // Reset the changes array. This listener is intentionally stateful and we start
        // from an empty change set.
        $this->changes = [];
        $this->inserts = [];

        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->flagForUpdate($entity, $entityManager);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->flagForUpdate($entity, $entityManager);
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $this->flagForDelete($entity, $entityManager);
        }

        foreach ($unitOfWork->getScheduledCollectionUpdates() as $collection) {
            foreach ($collection->getIterator() as $entity) {
                $this->flagForUpdate($entity, $entityManager);
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        // If we had no changes, no event needs to be dispatched.
        if (\count($this->changes) === 0 && \count($this->inserts) === 0) {
            return;
        }

        $entityManager = $args->getEntityManager();

        // Convert any inserts that had no identifiers into changes with identifiers
        // from after the flush.
        $this->processInserts($entityManager);

        // Dispatch an event with all changes that occurred in the flush.
        $this->dispatcher->dispatch(new EntityChangeEvent($this->changes));

        // Clean up so the listener is (almost) always in a pristine condition.
        $this->changes = [];
    }

    /**
     * @return string[]
     */
    private function calculateChangedProperties(object $entity, UnitOfWork $unitOfWork): array
    {
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        // Returns the keys of the change set that represent all changed properties on the entity.
        return \array_keys($changeSet);
    }

    /**
     * @param mixed $entity
     */
    private function flagForDelete($entity, EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getClassMetadata(\get_class($entity));

        /**
         * Resolve the proper class name to use when talking about this entity, which isn't going to
         * be the proxy class name.
         *
         * The name returned by ClassMetadata is a class string, which isnt annotated as such in
         * doctrine.
         *
         * @phpstan-var class-string
         */
        $className = $metadata->getName();

        // If we're not watching for changes for the class, there's nothing to flag.
        if ($this->isWatched($className) === false) {
            return;
        }

        $ids = $metadata->getIdentifierValues($entity);

        $metadata = $this->deleteEnrichment instanceof DeletedEntityEnrichmentInterface === true
            ? $this->deleteEnrichment->getMetadata($entity)
            : [];

        $this->changes[] = new DeletedEntity($className, $ids, $metadata);
    }

    private function flagForUpdate(object $entity, EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getClassMetadata(\get_class($entity));

        /**
         * Resolve the proper class name to use when talking about this entity, which isn't going to
         * be the proxy class name.
         *
         * The name returned by ClassMetadata is a class string, which isnt annotated as such in
         * doctrine.
         *
         * @phpstan-var class-string
         */
        $className = $metadata->getName();

        // If we're not watching for changes for the class, there's nothing to flag.
        if ($this->isWatched($className) === false) {
            return;
        }

        $entityIds = $metadata->getIdentifierValues($entity);
        $changedProperties = $this->calculateChangedProperties($entity, $entityManager->getUnitOfWork());

        // If we dont have any ids for the object we will store the DTO in a separate array to be re-processed
        // after the flush.
        if (\count($entityIds) === 0) {
            $this->inserts[] = [new UpdatedEntity($changedProperties, $className, $entityIds), $entity];

            return;
        }

        $this->changes[] = new UpdatedEntity($changedProperties, $className, $entityIds);
    }

    /**
     * @phpstan-param class-string $className
     */
    private function isWatched(string $className): bool
    {
        // If the watched array is null, we are not watching for anything specific and will emit
        // events for all classes.
        if ($this->watchedClasses === null) {
            return true;
        }

        return \in_array($className, $this->watchedClasses, true) === true;
    }

    private function processInserts(EntityManagerInterface $entityManager): void
    {
        foreach ($this->inserts as $insert) {
            $dto = $insert[0] ?? null;
            $entity = $insert[1] ?? null;

            if (($dto instanceof UpdatedEntity) === false || $entity === null) {
                // @codeCoverageIgnoreStart
                // Invalid insert data - cant normally get here.
                continue;
                // @codeCoverageIgnoreEnd
            }

            /**
             * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises ===
             *
             * @var \EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity $dto
             */

            $metadata = $entityManager->getClassMetadata(\get_class($entity));
            $ids = $metadata->getIdentifierValues($entity);
            if (\count($ids) === 0) {
                // @codeCoverageIgnoreStart
                // The object still has no ids, we cant emit a change for it.
                continue;
                // @codeCoverageIgnoreEnd
            }

            $this->changes[] = new UpdatedEntity($dto->getChangedProperties(), $dto->getClass(), $ids);
        }
    }
}
