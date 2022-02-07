<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Stubs;

use EonX\EasyBatch\Interfaces\AsyncDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;

final class AsyncDispatcherStub implements AsyncDispatcherInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectInterface[]
     */
    private $dispatched = [];

    public function dispatch(BatchObjectInterface $batchObject): void
    {
        if ($batchObject instanceof BatchItemInterface) {
            $this->dispatchItem($batchObject);
        }
    }

    public function dispatchItem(BatchItemInterface $batchItem): void
    {
        $this->dispatched[] = $batchItem;
    }

    /**
     * @return \EonX\EasyBatch\Interfaces\BatchObjectInterface[]
     */
    public function getDispatched(): array
    {
        return $this->dispatched;
    }
}
