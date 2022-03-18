<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Dispatchers;

interface DeferredEntityEventDispatcherInterface
{
    /**
     * @param string[] $objectClasses
     */
    public function addToDisableList(array $objectClasses): void;

    public function clear(?int $transactionNestingLevel = null): void;

    /**
     * @param int $transactionNestingLevel
     * @param object $object
     * @param array<string, array{mixed, mixed}> $entityChangeSet
     */
    public function deferDelete(int $transactionNestingLevel, object $object, array $entityChangeSet): void;

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

    /**
     * @param string[] $objectClasses
     */
    public function removeFromDisableList(array $objectClasses): void;
}
