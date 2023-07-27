<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Events;

use EonX\EasyBatch\Interfaces\BatchInterface;

final class BatchCreatedFromArrayEvent extends AbstractBatchEvent
{
    public function __construct(
        BatchInterface $batch,
        private readonly array $array,
    ) {
        parent::__construct($batch);
    }

    public function getArray(): array
    {
        return $this->array;
    }
}
