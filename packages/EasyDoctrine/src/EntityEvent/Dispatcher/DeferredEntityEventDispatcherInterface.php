<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Dispatcher;

interface DeferredEntityEventDispatcherInterface
{
    public function clear(?int $transactionNestingLevel = null): void;

    public function deferCollectionUpdate(
        int $transactionNestingLevel,
        object $entity,
        string $fieldName,
        array $oldIds,
        array $newsIds,
    ): void;

    public function deferDelete(int $transactionNestingLevel, object $entity, array $entityChangeSet): void;

    public function deferInsert(int $transactionNestingLevel, object $entity, array $entityChangeSet): void;

    /**
     * @todo Rename $object to $entity in next major release
     */
    public function deferUpdate(int $transactionNestingLevel, object $object, array $entityChangeSet): void;

    public function disable(): void;

    public function dispatch(): void;

    public function enable(): void;
}
