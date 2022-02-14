<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface AsyncDispatcherInterface
{
    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function dispatchItem(BatchItemInterface $batchItem): void;
}
