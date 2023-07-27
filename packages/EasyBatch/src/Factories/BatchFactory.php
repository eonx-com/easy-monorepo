<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Factories;

use EonX\EasyBatch\Events\BatchCreatedEvent;
use EonX\EasyBatch\Events\BatchCreatedFromArrayEvent;
use EonX\EasyBatch\Interfaces\BatchFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;

final class BatchFactory extends AbstractBatchObjectFactory implements BatchFactoryInterface
{
    public function createFromCallable(callable $itemsProvider, ?string $class = null): BatchInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->transformer->instantiateForClass($class);
        $batch->setItemsProvider($itemsProvider);

        /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->modifyBatchObject(new BatchCreatedEvent($batch));

        return $batch;
    }

    public function createFromIterable(iterable $items, ?string $class = null): BatchInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->transformer->instantiateForClass($class);
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
}
