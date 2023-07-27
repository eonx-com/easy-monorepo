<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Transformers;

use Carbon\Carbon;
use DateTimeInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectTransformerInterface;
use EonX\EasyUtils\Helpers\ErrorDetailsHelper;
use Throwable;

abstract class AbstractBatchObjectTransformer implements BatchObjectTransformerInterface
{
    private string $datetimeFormat;

    public function __construct(
        private readonly string $class,
        ?string $datetimeFormat = null,
    ) {
        $this->datetimeFormat = $datetimeFormat ?? BatchObjectInterface::DATETIME_FORMAT;
    }

    public function instantiateForClass(?string $class = null): BatchObjectInterface
    {
        $class ??= $this->class;
        /** @var \EonX\EasyBatch\Interfaces\BatchObjectInterface $classInstance */
        $classInstance = new $class();

        return $classInstance;
    }

    public function transformToArray(BatchObjectInterface $batchObject): array
    {
        return $this->formatData($this->doTransformToArray($batchObject));
    }

    public function transformToObject(array $data): BatchObjectInterface
    {
        $object = $this->instantiateForClass($data['class'] ?? null);
        $object
            ->setApprovalRequired((bool)($data['requires_approval'] ?? 0))
            ->setName($data['name'] ?? null)
            ->setId($data['id'])
            ->setStatus((string)($data['status'] ?? BatchObjectInterface::STATUS_PENDING));

        if (\is_string($data['metadata'] ?? null)) {
            $object->setMetadata(\json_decode($data['metadata'], true));
        }

        if (isset($data['throwable'])) {
            $object->setThrowableDetails(\json_decode((string)$data['throwable'], true) ?? []);
        }

        if (isset($data['type'])) {
            $object->setType((string)$data['type']);
        }

        $this->hydrateBatchObject($object, $data);
        $this->setDateTimes($object, $data);

        return $object;
    }

    abstract protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void;

    protected function doTransformToArray(BatchObjectInterface $batchObject): array
    {
        return $batchObject->toArray();
    }

    private function formatData(array $data): array
    {
        foreach ($data as $name => $value) {
            if (\is_array($value)) {
                $data[$name] = \json_encode($value);
            }

            if ($value instanceof DateTimeInterface) {
                $data[$name] = $value->format($this->datetimeFormat);
            }

            if ($value instanceof Throwable) {
                $data[$name] = \json_encode(ErrorDetailsHelper::resolveSimpleDetails($value));
            }
        }

        return $data;
    }

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
