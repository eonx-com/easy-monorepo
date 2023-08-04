<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Dispatchers;

interface DeferredEntityEventDispatcherInterface
{
    public function clear(?int $transactionNestingLevel = null): void;

    /**
     * @param mixed[] $oldIds
     * @param mixed[] $newsIds
     */
    public function deferCollectionUpdate(
        int $transactionNestingLevel,
        object $entity,
        string $fieldName,
        array $oldIds,
        array $newsIds,
    ): void;

    /**
     * @param mixed[] $entityChangeSet
     */
    public function deferDelete(int $transactionNestingLevel, object $entity, array $entityChangeSet): void;

    /**
     * @param mixed[] $entityChangeSet
     */
    public function deferInsert(int $transactionNestingLevel, object $entity, array $entityChangeSet): void;

    /**
     * @param mixed[] $entityChangeSet
     */
    public function deferUpdate(int $transactionNestingLevel, object $entity, array $entityChangeSet): void;

    public function disable(): void;

    public function dispatch(): void;

    public function enable(): void;
}
