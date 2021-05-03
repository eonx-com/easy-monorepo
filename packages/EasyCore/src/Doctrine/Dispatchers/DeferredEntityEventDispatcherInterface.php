<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Dispatchers;

interface DeferredEntityEventDispatcherInterface
{
    public function clear(?int $transactionNestingLevel = null): void;

    /**
     * @param \EonX\EasyCore\Interfaces\DatabaseEntityInterface[] $entityInsertions
     */
    public function deferInsertions(array $entityInsertions, int $transactionNestingLevel): void;

    /**
     * @param \EonX\EasyCore\Interfaces\DatabaseEntityInterface[] $entityUpdates
     */
    public function deferUpdates(array $entityUpdates, int $transactionNestingLevel): void;

    public function disable(): void;

    public function dispatch(): void;

    public function enable(): void;
}
