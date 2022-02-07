<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface AsyncDispatcherInterface
{
    /**
     * @deprecated since 3.4, will be removed in 4.0. Use dispatchItem() instead.
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatch(BatchObjectInterface $batchObject): void;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatchItem(BatchItemInterface $batchItem): void;
}
