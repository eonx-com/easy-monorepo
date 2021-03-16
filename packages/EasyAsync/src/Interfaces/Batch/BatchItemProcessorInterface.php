<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchItemProcessorInterface
{
    /**
     * @return mixed The return from $func
     *
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchCancelledException
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException
     */
    public function process(BatchItemInterface $batchItem, callable $func);
}
