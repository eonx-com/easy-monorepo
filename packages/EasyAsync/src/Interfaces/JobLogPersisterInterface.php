<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
interface JobLogPersisterInterface
{
    public function findForJob(string $jobId, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface;

    public function persist(JobLogInterface $jobLog): JobLogInterface;

    public function removeForJob(string $jobId): void;
}
