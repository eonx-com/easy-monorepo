<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemProcessorInterface
{
    /**
     * @return mixed The return from $func
     *
     * @throws \EonX\EasyBatch\Exceptions\Batch\BatchCancelledException
     * @throws \EonX\EasyBatch\Exceptions\Batch\BatchNotFoundException
     */
    public function process(BatchItemInterface $batchItem, callable $func);
}
