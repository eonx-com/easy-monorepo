<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use Carbon\Carbon;
use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class BatchFactory implements BatchFactoryInterface
{
    /**
     * @var string[]
     */
    private const DATE_TIMES = [
        'cancelled_at' => 'setCancelledAt',
        'finished_at' => 'setFinishedAt',
        'started_at' => 'setStartedAt',
        'created_at' => 'setCreatedAt',
        'updated_at' => 'setUpdatedAt',
    ];

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $datetimeFormat;

    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    public function __construct(RandomGeneratorInterface $random, ?string $class = null, ?string $datetimeFormat = null)
    {
        $this->random = $random;
        $this->class = $class ?? Batch::class;
        $this->datetimeFormat = $datetimeFormat ?? BatchStoreInterface::DATETIME_FORMAT;
    }

    public function createFromCallable(callable $itemsProvider, ?string $class = null): BatchInterface
    {
        $batch = $this
            ->instantiateBatch($class)
            ->setItemsProvider($itemsProvider);

        return $this->modifyBatch($batch);
    }

    /**
     * @param iterable<object> $items
     */
    public function createFromIterable(iterable $items, ?string $class = null): BatchInterface
    {
        $batch = $this
            ->instantiateBatch($class)
            ->setItems($items);

        return $this->modifyBatch($batch);
    }

    /**
     * @param object $item
     */
    public function createFromObject($item, ?string $class = null): BatchInterface
    {
        return $this->createFromIterable([$item], $class);
    }

    /**
     * @param mixed[] $data
     */
    public function instantiateFromArray(array $data): BatchInterface
    {
        $batch = $this->instantiateBatch($data['class'] ?? null);

        $batch
            ->setId($data['id'])
            ->setName($data['name'] ?? null)
            ->setFailed((int)($data['failed'] ?? 0))
            ->setProcessed((int)($data['processed'] ?? 0))
            ->setStatus($data['status'] ?? BatchInterface::STATUS_PENDING)
            ->setSucceeded((int)($data['succeeded'] ?? 0))
            ->setTotal((int)($data['total'] ?? 0));

        foreach (self::DATE_TIMES as $name => $setter) {
            if (isset($data[$name])) {
                $datetime = Carbon::createFromFormat($this->datetimeFormat, $data[$name]);

                if ($datetime instanceof Carbon) {
                    $batch->{$setter}($datetime);
                }
            }
        }

        return $batch;
    }

    private function instantiateBatch(?string $class = null): BatchInterface
    {
        $class = $class ?? $this->class;

        return new $class();
    }

    private function modifyBatch(BatchInterface $batch): BatchInterface
    {
        return $batch->setId($this->random->uuidV4());
    }
}
