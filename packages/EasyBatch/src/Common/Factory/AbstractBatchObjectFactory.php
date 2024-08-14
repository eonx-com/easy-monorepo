<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Factory;

use EonX\EasyBatch\Common\Event\AbstractBatchObjectEvent;
use EonX\EasyBatch\Common\Transformer\BatchObjectTransformerInterface;
use EonX\EasyBatch\Common\ValueObject\AbstractBatchObject;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;

abstract class AbstractBatchObjectFactory
{
    public function __construct(
        protected readonly BatchObjectTransformerInterface $transformer,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
    }

    public function createFromArray(array $data): AbstractBatchObject
    {
        $batchObject = $this->transformer->transformToObject($data);
        $eventClass = $this->getCreatedFromArrayEventClass();
        /** @var \EonX\EasyBatch\Common\Event\AbstractBatchObjectEvent $eventClassInstance */
        $eventClassInstance = new $eventClass($batchObject, $data);

        return $this->modifyBatchObject($eventClassInstance);
    }

    abstract protected function getCreatedFromArrayEventClass(): string;

    protected function modifyBatchObject(AbstractBatchObjectEvent $event): AbstractBatchObject
    {
        $this->dispatcher?->dispatch($event);

        return $event->getBatchObject();
    }
}
