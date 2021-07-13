<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Transformers;

use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Objects\Batch;

final class BatchTransformer extends AbstractBatchObjectTransformer
{
    public function __construct(?string $class = null, ?string $datetimeFormat = null)
    {
        parent::__construct($class ?? Batch::class, $datetimeFormat);
    }

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchInterface $batchObject
     * @param mixed[] $data
     */
    protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void
    {
        $batchObject
            ->setFailed((int)($data['failed'] ?? 0))
            ->setProcessed((int)($data['processed'] ?? 0))
            ->setSucceeded((int)($data['succeeded'] ?? 0))
            ->setTotal((int)($data['total'] ?? 0));
    }
}
