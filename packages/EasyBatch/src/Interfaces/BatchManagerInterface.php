<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchManagerInterface
{
    /**
     * @var int
     */
    public const DEFAULT_BATCH_ITEMS_PER_PAGE = 15;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectNotSupportedException
     */
    public function approve(BatchObjectInterface $batchObject): BatchObjectInterface;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectNotSupportedException
     */
    public function cancel(BatchObjectInterface $batchObject): BatchObjectInterface;

    /**
     * @param int|string $batchId
     */
    public function iterateThroughItems($batchId, ?string $dependsOnName, callable $func): void;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatch(BatchInterface $batch): BatchInterface;

    public function dispatchItem(BatchItemInterface $batchItem): BatchItemInterface;

    /**
     * @return mixed
     */
    public function processItem(BatchInterface $batch, BatchItemInterface $batchItem, callable $func);
}
