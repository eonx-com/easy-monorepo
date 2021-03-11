<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use Carbon\Carbon;
use EonX\EasyAsync\Interfaces\Batch\BatchInstantiatorInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;

final class BatchInstantiator implements BatchInstantiatorInterface
{
    /**
     * @var string[]
     */
    private const DATE_TIMES = [
        'finished_at' => 'setFinishedAt',
        'started_at' => 'setStartedAt',
        'created_at' => 'setCreatedAt',
        'updated_at' => 'setUpdatedAt',
    ];

    /**
     * @var string
     */
    private $datetimeFormat;

    public function __construct(?string $datetimeFormat = null)
    {
        $this->datetimeFormat = $datetimeFormat ?? BatchStoreInterface::DATETIME_FORMAT;
    }

    /**
     * @param mixed[] $data
     */
    public function instantiateFromArray(array $data): BatchInterface
    {
        $batch = (new Batch())
            ->setId($data['id'])
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
}
