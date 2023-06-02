<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Factories;

use EonX\EasyBatch\Events\AbstractBatchObjectEvent;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectTransformerInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

abstract class AbstractBatchObjectFactory
{
    public function __construct(
        protected readonly BatchObjectTransformerInterface $transformer,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
    }

    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): BatchObjectInterface
    {
        $batchObject = $this->transformer->transformToObject($data);
        $eventClass = $this->getCreatedFromArrayEventClass();
        /** @var \EonX\EasyBatch\Events\AbstractBatchObjectEvent $eventClassInstance */
        $eventClassInstance = new $eventClass($batchObject, $data);

        return $this->modifyBatchObject($eventClassInstance);
    }

    abstract protected function getCreatedFromArrayEventClass(): string;

    protected function modifyBatchObject(AbstractBatchObjectEvent $event): BatchObjectInterface
    {
        $this->dispatcher?->dispatch($event);

        return $event->getBatchObject();
    }
}
