<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Events;

use EonX\EasyBatch\Interfaces\BatchItemInterface;

final class BatchItemCreatedFromArrayEvent extends AbstractBatchItemEvent
{
    /**
     * @param mixed[] $array
     */
    public function __construct(
        BatchItemInterface $batchItem,
        private readonly array $array,
    ) {
        parent::__construct($batchItem);
    }

    /**
     * @return mixed[]
     */
    public function getArray(): array
    {
        return $this->array;
    }
}
