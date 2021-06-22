<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Factories;

use Carbon\Carbon;
use EonX\EasyBatch\Events\AbstractBatchObjectEvent;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

abstract class AbstractBatchObjectFactory
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $datetimeFormat;

    /**
     * @var null|\EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        string $class,
        ?string $datetimeFormat = null,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        $this->class = $class;
        $this->datetimeFormat = $datetimeFormat ?? BatchObjectInterface::DATETIME_FORMAT;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): BatchObjectInterface
    {
        $batchObject = $this->instantiateBatchObject($data['class'] ?? null);
        $eventClass = $this->getCreatedFromArrayEventClass();

        $this->hydrateBatchObject($batchObject, $data);
        $this->setDateTimes($batchObject, $data);

        return $this->modifyBatchObject(new $eventClass($batchObject, $data));
    }

    abstract protected function getCreatedFromArrayEventClass(): string;

    /**
     * @param mixed[] $data
     */
    abstract protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void;

    protected function instantiateBatchObject(?string $class = null): BatchObjectInterface
    {
        $class = $class ?? $this->class;

        return new $class();
    }

    protected function modifyBatchObject(AbstractBatchObjectEvent $event): BatchObjectInterface
    {
        if ($this->dispatcher) {
            $this->dispatcher->dispatch($event);
        }

        return $event->getBatchObject();
    }

    /**
     * @param mixed[] $data
     */
    private function setDateTimes(BatchObjectInterface $batchObject, array $data): void
    {
        foreach (BatchObjectInterface::DATE_TIMES as $name => $setter) {
            if (isset($data[$name])) {
                // Allow DateTimes to be instantiated already
                $datetime = $data[$name] instanceof Carbon
                    ? $data[$name]
                    : Carbon::createFromFormat($this->datetimeFormat, $data[$name]);

                if ($datetime instanceof Carbon) {
                    $batchObject->{$setter}($datetime);
                }
            }
        }
    }
}
