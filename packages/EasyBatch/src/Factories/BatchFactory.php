<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Factories;

use EonX\EasyBatch\Events\BatchCreatedEvent;
use EonX\EasyBatch\Events\BatchCreatedFromArrayEvent;
use EonX\EasyBatch\Interfaces\BatchFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Objects\Batch;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchFactory extends AbstractBatchObjectFactory implements BatchFactoryInterface
{
    public function __construct(
        ?string $class = null,
        ?string $dateTimeFormat = null,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct($class ?? Batch::class, $dateTimeFormat, $dispatcher);
    }

    public function createFromCallable(callable $itemsProvider, ?string $class = null): BatchInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->instantiateBatchObject($class);
        $batch->setItemsProvider($itemsProvider);

        /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->modifyBatchObject(new BatchCreatedEvent($batch));

        return $batch;
    }

    public function createFromIterable(iterable $items, ?string $class = null): BatchInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->instantiateBatchObject($class);
        $batch->setItems($items);

        /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->modifyBatchObject(new BatchCreatedEvent($batch));

        return $batch;
    }

    public function createFromObject(object $item, ?string $class = null): BatchInterface
    {
        return $this->createFromIterable([$item], $class);
    }

    protected function getCreatedFromArrayEventClass(): string
    {
        return BatchCreatedFromArrayEvent::class;
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
            ->setTotal((int)($data['total'] ?? 0))
            ->setName($data['name'] ?? null)
            ->setStatus($data['status'] ?? BatchInterface::STATUS_PENDING)
            ->setId($data['id']);
    }
}
