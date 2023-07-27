<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Transformers;

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
     */
    protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void
    {
        $batchObject
            ->setCancelled((int)($data['cancelled'] ?? 0))
            ->setFailed((int)($data['failed'] ?? 0))
            ->setProcessed((int)($data['processed'] ?? 0))
            ->setSucceeded((int)($data['succeeded'] ?? 0))
            ->setTotal((int)($data['total'] ?? 0));

        if (isset($data['parent_batch_item_id'])) {
            $batchObject->setParentBatchItemId((string)$data['parent_batch_item_id']);
        }
    }
}
