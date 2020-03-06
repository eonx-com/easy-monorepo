<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobInterface extends EasyAsyncDataInterface
{
    public const STATUSES = [
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
        self::STATUS_DELETED,
        self::STATUS_FAILED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_SCHEDULED,
    ];

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_DELETED = 'deleted';

    public const STATUS_FAILED = 'failed';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SCHEDULED = 'scheduled';

    public function getFailed(): int;

    public function getProcessed(): int;

    public function getSucceeded(): int;

    public function getTotal(): int;

    public function setFailed(int $failed): self;

    public function setProcessed(int $processed): self;

    public function setSucceeded(int $succeeded): self;
}
