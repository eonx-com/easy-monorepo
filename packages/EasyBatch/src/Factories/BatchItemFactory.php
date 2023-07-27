<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Factories;

use EonX\EasyBatch\Events\BatchItemCreatedEvent;
use EonX\EasyBatch\Events\BatchItemCreatedFromArrayEvent;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;

final class BatchItemFactory extends AbstractBatchObjectFactory implements BatchItemFactoryInterface
{
    public function create(int|string $batchId, ?object $message = null, ?string $class = null): BatchItemInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->transformer->instantiateForClass($class);
        $batchItem->setBatchId($batchId);

        if ($message !== null) {
            $batchItem->setMessage($message);
        }

        if ($message === null) {
            $batchItem
                ->setStatus(BatchItemInterface::STATUS_BATCH_PENDING_APPROVAL)
                ->setType(BatchItemInterface::TYPE_NESTED_BATCH);
        }

        /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->modifyBatchObject(new BatchItemCreatedEvent($batchItem));

        return $batchItem;
    }

    protected function getCreatedFromArrayEventClass(): string
    {
        return BatchItemCreatedFromArrayEvent::class;
    }
}
