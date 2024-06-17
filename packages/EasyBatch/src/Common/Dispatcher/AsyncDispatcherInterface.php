<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Dispatcher;

use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;

interface AsyncDispatcherInterface
{
    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemInvalidException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function dispatchItem(BatchItemInterface $batchItem): void;
}
