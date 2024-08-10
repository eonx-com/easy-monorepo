<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Event;

use EonX\EasyBatch\Common\ValueObject\Batch;

final class BatchCreatedFromArrayEvent extends AbstractBatchEvent
{
    public function __construct(
        Batch $batch,
        private readonly array $array,
    ) {
        parent::__construct($batch);
    }

    public function getArray(): array
    {
        return $this->array;
    }
}
