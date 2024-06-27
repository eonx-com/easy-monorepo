<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Factory;

use EonX\EasyBatch\Common\Event\BatchCreatedEvent;
use EonX\EasyBatch\Common\Event\BatchCreatedFromArrayEvent;
use EonX\EasyBatch\Common\ValueObject\BatchInterface;

final class BatchFactory extends AbstractBatchObjectFactory implements BatchFactoryInterface
{
    public function createFromCallable(callable $itemsProvider, ?string $class = null): BatchInterface
    {
        /** @var \EonX\EasyBatch\Common\ValueObject\BatchInterface $batch */
        $batch = $this->transformer->instantiateForClass($class);
        $batch->setItemsProvider($itemsProvider);

        /** @var \EonX\EasyBatch\Common\ValueObject\BatchInterface $batch */
        $batch = $this->modifyBatchObject(new BatchCreatedEvent($batch));

        return $batch;
    }

    public function createFromIterable(iterable $items, ?string $class = null): BatchInterface
    {
        /** @var \EonX\EasyBatch\Common\ValueObject\BatchInterface $batch */
        $batch = $this->transformer->instantiateForClass($class);
        $batch->setItems($items);

        /** @var \EonX\EasyBatch\Common\ValueObject\BatchInterface $batch */
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
}
