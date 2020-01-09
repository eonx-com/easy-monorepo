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
     * Stores an array of entities that are to be inserted that may not yet have primary ids.
     *
     * @var mixed[]
     */
    private $inserts = [];

    /**
     * @var \EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface|null
     */
    private $deleteEnrichment;

    /**
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Stores an array of class names we're watching for updates. If null, we will watch for
     * any changes, if an array, we will only dispatch when we see a change of the given classes.
     *
     * @var null|string[]
     */
    private $watchedClasses;

    /**
     * Constructor.
     *
     * @param \Psr\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface|null $deleteEnrichment
     * @param string[]|null $watchedClasses
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
        // Reset the changes array. This listener is intentionally stateful and we start
        // from an empty change set.
        $this->changes = [];

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

    /**
     * Dispatches jobs for search index updates.
     *
     * @param \Doctrine\ORM\Event\PostFlushEventArgs $args
     *
     * @return void
     */
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
     * Takes any changes that were detected from objects that had no identifiers and re-queries
     * for identifiers.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return void
     */
    protected function processInserts(EntityManagerInterface $entityManager): void
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
             * @var \EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity $dto
             *
             * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises ===
             */

            $metadata = $entityManager->getClassMetadata(\get_class($entity));
            $ids = $metadata->getIdentifierValues($entity);
            if (\count($ids) === 0) {
                // @codeCoverageIgnoreStart
                // The object still has no ids, we cant emit a change for it.
                continue;
                // @codeCoverageIgnoreEnd
            }

            $this->changes[] = new UpdatedEntity(
                $dto->getChangedProperties(),
                $dto->getClass(),
                $ids
            );
        }
    }

    /**
     * Attempts to discover any changed properties from the doctrine UnitOfWork.
     *
     * @param object $entity
     * @param \Doctrine\ORM\UnitOfWork $unitOfWork
     *
     * @return string[]
     */
    private function calculateChangedProperties(object $entity, UnitOfWork $unitOfWork): array
    {
        $changeset = $unitOfWork->getEntityChangeSet($entity);

        // Returns the keys of the change set that represent all changed properties on the entity.
        return \array_keys($changeset);
    }

    /**
     * Collects actual deleted entities so they can be processed into relevant information
     * for queue workers to handle.
     *
     * @param mixed $entity
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return void
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
            $this->inserts[] = [
                new UpdatedEntity($changedProperties, $className, $entityIds),
                $entity
            ];

            return;
        }

        $this->changes[] = new UpdatedEntity($changedProperties, $className, $entityIds);
    }

    /**
     * Checks if the class name is in our array of watched classes.
     *
     * @phpstan-param class-string $className
     *
     * @param string $className
     *
     * @return bool
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
}
