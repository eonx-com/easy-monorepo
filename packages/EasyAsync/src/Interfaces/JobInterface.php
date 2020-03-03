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
        self::STATUS_SCHEDULED
    ];

    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DELETED = 'deleted';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SCHEDULED = 'scheduled';

    /**
     * Get number of failed job logs.
     *
     * @return int
     */
    public function getFailed(): int;

    /**
     * Get number of processed job logs.
     *
     * @return int
     */
    public function getProcessed(): int;

    /**
     * Get number of succeeded job logs.
     *
     * @return int
     */
    public function getSucceeded(): int;

    /**
     * Get total of job logs to process.
     *
     * @return int
     */
    public function getTotal(): int;

    /**
     * Set count of failed job logs.
     *
     * @param int $failed
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public function setFailed(int $failed): JobInterface;

    /**
     * Set count of processed job logs.
     *
     * @param int $processed
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public function setProcessed(int $processed): JobInterface;

    /**
     * Set count of succeeded job logs.
     *
     * @param int $succeeded
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public function setSucceeded(int $succeeded): JobInterface;
}
