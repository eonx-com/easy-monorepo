<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Factories;

use EonX\EasyBatch\Events\AbstractBatchObjectEvent;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectTransformerInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

abstract class AbstractBatchObjectFactory
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectTransformerInterface
     */
    protected $transformer;

    /**
     * @var null|\EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        BatchObjectTransformerInterface $transformer,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        $this->transformer = $transformer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): BatchObjectInterface
    {
        $batchObject = $this->transformer->transformToObject($data);
        $eventClass = $this->getCreatedFromArrayEventClass();

        return $this->modifyBatchObject(new $eventClass($batchObject, $data));
    }

    abstract protected function getCreatedFromArrayEventClass(): string;

    protected function modifyBatchObject(AbstractBatchObjectEvent $event): BatchObjectInterface
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch($event);
        }

        return $event->getBatchObject();
    }
}
