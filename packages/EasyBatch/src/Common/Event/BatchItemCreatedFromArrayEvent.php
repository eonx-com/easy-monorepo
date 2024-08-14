<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Event;

use EonX\EasyBatch\Common\ValueObject\BatchItem;

final class BatchItemCreatedFromArrayEvent extends AbstractBatchItemEvent
{
    public function __construct(
        BatchItem $batchItem,
        private readonly array $array,
    ) {
        parent::__construct($batchItem);
    }

    public function getArray(): array
    {
        return $this->array;
    }
}
