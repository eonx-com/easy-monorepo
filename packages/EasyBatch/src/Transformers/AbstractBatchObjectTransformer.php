<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Transformers;

use Carbon\Carbon;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectTransformerInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

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
        /** @var \EonX\EasyBatch\Interfaces\BatchObjectInterface $classInstance */
        $classInstance = new $class();

        return $classInstance;
    }

    /**
     * @return mixed[]
     */
    public function transformToArray(BatchObjectInterface $batchObject): array
    {
        return $this->formatData($this->doTransformToArray($batchObject));
    }

    public function transformToObject(array $data): BatchObjectInterface
    {
        $object = $this->instantiateForClass($data['class'] ?? null);
        $object
            ->setName($data['name'] ?? null)
            ->setId($data['id'])
            ->setStatus((string)($data['status'] ?? BatchObjectInterface::STATUS_PENDING));

        if (\is_string($data['metadata'] ?? null)) {
            $object->setMetadata(\json_decode($data['metadata'], true));
        }

        if (isset($data['throwable'])) {
            /** @var \Throwable $throwable */
            $throwable = $this->unserialize((string)$data['throwable']);
            $object->setThrowable($throwable);
        }

        if (isset($data['type'])) {
            $object->setType((string)$data['type']);
        }

        $this->hydrateBatchObject($object, $data);
        $this->setDateTimes($object, $data);

        return $object;
    }

    /**
     * @param mixed[] $data
     */
    abstract protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void;

    /**
     * @return mixed[]
     */
    protected function doTransformToArray(BatchObjectInterface $batchObject): array
    {
        return $batchObject->toArray();
    }

    protected function serialize(object $message): string
    {
        if ($message instanceof HandlerFailedException) {
            $envelope = $message->getEnvelope()
                ->withoutAll('Symfony\Component\Messenger\Stamp\AckStamp');
            $message = new HandlerFailedException($envelope, $message->getNestedExceptions());
        }

        $body = \addslashes(\serialize($message));

        if (\preg_match('//u', $body) === false) {
            $body = \base64_encode($body);
        }

        return $body;
    }

    protected function unserialize(string $message): object
    {
        if (\strpos($message, '}', -1) === false) {
            /** @var string $message */
            $message = \base64_decode($message, true);
        }

        return \unserialize(\stripslashes($message));
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function formatData(array $data): array
    {
        foreach ($data as $name => $value) {
            if (\is_array($value)) {
                $data[$name] = \json_encode($value);
            }

            if ($value instanceof \DateTimeInterface) {
                $data[$name] = $value->format($this->datetimeFormat);
            }

            if ($value instanceof \Throwable) {
                $data[$name] = $this->serialize($value);
            }
        }

        return $data;
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
