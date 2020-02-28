<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

interface JobPersisterInterface
{
    /**
     * Find one job for given jobId.
     *
     * @param string $jobId
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToFindJobException
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function find(string $jobId): JobInterface;

    /**
     * Find paginated list of jobs for given target.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForTarget(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface;

    /**
     * Find paginated list of jobs for given target type.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForTargetType(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface;

    /**
     * Find paginated list of jobs for given type.
     *
     * @param string $type
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForType(string $type, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface;

    /**
     * Find one job for given jobId and lock it for update.
     *
     * @param string $jobId
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToFindJobException
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function findOneForUpdate(string $jobId): JobInterface;

    /**
     * Persist given job.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobException
     */
    public function persist(JobInterface $job): JobInterface;

    /**
     * Remove given job.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return void
     */
    public function remove(JobInterface $job): void;
}
