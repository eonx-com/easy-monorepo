<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Dispatchers;

/**
 * @deprecated since 3.5, will be removed in 4.0. Use EasyDoctrine instead.
 */
interface DeferredEntityEventDispatcherInterface
{
    public function clear(?int $transactionNestingLevel = null): void;

    /**
     * @param object[] $entityInsertions
     */
    public function deferInsertions(array $entityInsertions, int $transactionNestingLevel): void;

    /**
     * @param object[] $entityUpdates
     */
    public function deferUpdates(array $entityUpdates, int $transactionNestingLevel): void;

    public function disable(): void;

    public function dispatch(): void;

    public function enable(): void;
}
