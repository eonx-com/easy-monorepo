<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Stubs;

use EonX\EasyBatch\Interfaces\AsyncDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;

final class AsyncDispatcherStub implements AsyncDispatcherInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectInterface[]
     */
    private $dispatched = [];

    public function dispatch(BatchObjectInterface $batchObject): void
    {
        $this->dispatched[] = $batchObject;
    }

    /**
     * @return \EonX\EasyBatch\Interfaces\BatchObjectInterface[]
     */
    public function getDispatched(): array
    {
        return $this->dispatched;
    }
}
