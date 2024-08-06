<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Transformer;

use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;

final class BatchTransformer extends AbstractBatchObjectTransformer
{
    /**
     * @param \EonX\EasyBatch\Common\ValueObject\BatchInterface $batchObject
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
