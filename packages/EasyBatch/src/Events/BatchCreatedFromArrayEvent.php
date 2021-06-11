<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Events;

use EonX\EasyBatch\Interfaces\BatchInterface;

final class BatchCreatedFromArrayEvent extends AbstractBatchEvent
{
    /**
     * @var mixed[]
     */
    private $array;

    /**
     * @param mixed[] $array
     */
    public function __construct(BatchInterface $batch, array $array)
    {
        $this->array = $array;

        parent::__construct($batch);
    }

    /**
     * @return mixed[]
     */
    public function getArray(): array
    {
        return $this->array;
    }
}
