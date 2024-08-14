<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Factory;

use EonX\EasyBatch\Common\Enum\BatchItemType;
use EonX\EasyBatch\Common\Enum\BatchObjectStatus;
use EonX\EasyBatch\Common\Event\BatchItemCreatedEvent;
use EonX\EasyBatch\Common\Event\BatchItemCreatedFromArrayEvent;
use EonX\EasyBatch\Common\ValueObject\BatchItem;

final class BatchItemFactory extends AbstractBatchObjectFactory implements BatchItemFactoryInterface
{
    public function create(int|string $batchId, ?object $message = null, ?string $class = null): BatchItem
    {
        /** @var \EonX\EasyBatch\Common\ValueObject\BatchItem $batchItem */
        $batchItem = $this->transformer->instantiateForClass($class);
        $batchItem->setBatchId($batchId);

        if ($message !== null) {
            $batchItem->setMessage($message);
        }

        if ($message === null) {
            $batchItem
                ->setStatus(BatchObjectStatus::BatchPendingApproval)
                ->setType(BatchItemType::NestedBatch->value);
        }

        /** @var \EonX\EasyBatch\Common\ValueObject\BatchItem $batchItem */
        $batchItem = $this->modifyBatchObject(new BatchItemCreatedEvent($batchItem));

        return $batchItem;
    }

    protected function getCreatedFromArrayEventClass(): string
    {
        return BatchItemCreatedFromArrayEvent::class;
    }
}
