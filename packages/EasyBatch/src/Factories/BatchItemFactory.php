<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Factories;

use EonX\EasyBatch\Objects\BatchItem;
use EonX\EasyBatch\Events\BatchItemCreatedEvent;
use EonX\EasyBatch\Events\BatchItemCreatedFromArrayEvent;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchItemFactory extends AbstractBatchObjectFactory implements BatchItemFactoryInterface
{
    public function __construct(
        ?string $class = null,
        ?string $dateTimeFormat = null,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct($class ?? BatchItem::class, $dateTimeFormat, $dispatcher);
    }

    /**
     * @param int|string $batchId
     */
    public function create($batchId, string $targetClass, ?string $class = null): BatchItemInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->instantiateBatchObject($class);
        $batchItem
            ->setBatchId($batchId)
            ->setTargetClass($targetClass);

        /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->modifyBatchObject(new BatchItemCreatedEvent($batchItem));

        return $batchItem;
    }

    protected function getCreatedFromArrayEventClass(): string
    {
        return BatchItemCreatedFromArrayEvent::class;
    }

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchItemInterface $batchObject
     * @param mixed[] $data
     */
    protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void
    {
        $batchObject
            ->setAttempts((int)($data['attempts'] ?? 0))
            ->setBatchId((string)$data['batch_id'])
            ->setTargetClass((string)$data['target_class'])
            ->setStatus((string)($data['status'] ?? BatchItemInterface::STATUS_PENDING))
            ->setId($data['id']);

        if (isset($data['reason'])) {
            $batchObject->setReason((string)$data['reason']);
        }

        if (isset($data['reason_params'])) {
            $batchObject->setReasonParams((array)$data['reason_params']);
        }
    }
}
