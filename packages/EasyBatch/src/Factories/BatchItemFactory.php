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
    public function create($batchId, ?object $message = null, ?string $class = null): BatchItemInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->instantiateBatchObject($class);
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

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchItemInterface $batchObject
     * @param mixed[] $data
     */
    protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void
    {
        $batchObject
            ->setApprovalRequired((bool)($data['requires_approval'] ?? 0))
            ->setAttempts((int)($data['attempts'] ?? 0))
            ->setBatchId((string)$data['batch_id'])
            ->setType((string)($data['type'] ?? BatchItemInterface::TYPE_MESSAGE))
            ->setStatus((string)($data['status'] ?? BatchItemInterface::STATUS_PENDING))
            ->setId($data['id']);

        if (isset($data['message'])) {
            $batchObject->setMessage(\unserialize((string)$data['message']));
        }

        if (isset($data['name'])) {
            $batchObject->setName((string)$data['name']);
        }

        if (isset($data['depends_on_name'])) {
            $batchObject->setDependsOnName((string)$data['depends_on_name']);
        }
    }
}
