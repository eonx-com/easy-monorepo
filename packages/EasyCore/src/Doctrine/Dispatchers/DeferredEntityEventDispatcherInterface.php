<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Dispatchers;

interface DeferredEntityEventDispatcherInterface
{
    public function clear(?int $transactionNestingLevel = null): void;

    /**
     * @param int $transactionNestingLevel
     * @param object $object
     * @param array<string, array{mixed, mixed}> $entityChangeSet
     */
    public function deferInsert(int $transactionNestingLevel, object $object, array $entityChangeSet): void;

    /**
     * @param int $transactionNestingLevel
     * @param object $object
     * @param array<string, array{mixed, mixed}> $entityChangeSet
     */
    public function deferUpdate(int $transactionNestingLevel, object $object, array $entityChangeSet): void;

    public function disable(): void;

    public function dispatch(): void;

    public function enable(): void;
}
