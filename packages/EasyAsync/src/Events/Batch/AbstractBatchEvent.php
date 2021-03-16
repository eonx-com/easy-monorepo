<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Events\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;

abstract class AbstractBatchEvent implements EasyAsyncEventInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchInterface
     */
    private $batch;

    public function __construct(BatchInterface $batch)
    {
        $this->batch = $batch;
    }

    public function getBatch(): BatchInterface
    {
        return $this->batch;
    }
}
