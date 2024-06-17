<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Event;

use EonX\EasyBatch\Common\ValueObject\BatchInterface;

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
