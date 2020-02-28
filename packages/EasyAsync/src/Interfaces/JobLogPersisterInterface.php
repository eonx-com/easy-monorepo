<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

interface JobLogPersisterInterface
{
    /**
     * Find paginated list of job logs for given job.
     *
     * @param string $jobId
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForJob(string $jobId, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface;

    /**
     * Persist given job log.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobLogException
     */
    public function persist(JobLogInterface $jobLog): JobLogInterface;
}
