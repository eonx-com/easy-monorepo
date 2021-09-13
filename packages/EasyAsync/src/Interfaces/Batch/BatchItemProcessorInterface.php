<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
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
