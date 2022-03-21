<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Dispatchers;

interface DeferredEntityEventDispatcherInterface
{
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

    /**
     * @param string[]|null $objectClasses
     */
    public function disable(?array $objectClasses = null): void;

    public function dispatch(): void;

    /**
     * @param string[]|null $objectClasses
     */
    public function enable(?array $objectClasses = null): void;
}
