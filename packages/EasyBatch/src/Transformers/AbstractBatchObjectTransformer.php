<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Transformers;

use Carbon\Carbon;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectTransformerInterface;

abstract class AbstractBatchObjectTransformer implements BatchObjectTransformerInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $datetimeFormat;

    public function __construct(string $class, ?string $datetimeFormat = null)
    {
        $this->class = $class;
        $this->datetimeFormat = $datetimeFormat ?? BatchObjectInterface::DATETIME_FORMAT;
    }

    public function instantiateForClass(?string $class = null): BatchObjectInterface
    {
        $class = $class ?? $this->class;

        return new $class();
    }

    /**
     * @return mixed[]
     */
    public function transformToArray(BatchObjectInterface $batchObject): array
    {
        return $batchObject->toArray();
    }

    public function transformToObject(array $data): BatchObjectInterface
    {
        $object = $this->instantiateForClass($data['class'] ?? null);

        $this->hydrateBatchObject($object, $data);
        $this->setDateTimes($object, $data);

        return $object;
    }

    /**
     * @param mixed[] $data
     */
    abstract protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void;

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
