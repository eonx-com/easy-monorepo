<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Dispatchers;

interface DeferredEntityEventDispatcherInterface
{
    /**
     * @param int $transactionNestingLevel
     * @param string $oid
     * @param array<string, array{mixed, mixed}> $entityChangeSet
     */
    public function addEntityChangeSet(int $transactionNestingLevel, string $oid, array $entityChangeSet): void;

    public function clear(?int $transactionNestingLevel = null): void;

    public function deferInsert(int $transactionNestingLevel, string $oid, object $entity): void;

    public function deferUpdate(int $transactionNestingLevel, string $oid, object $entity): void;

    public function disable(): void;

    public function dispatch(): void;

    public function enable(): void;
}
