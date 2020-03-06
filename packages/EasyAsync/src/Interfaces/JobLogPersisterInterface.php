<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

interface JobLogPersisterInterface
{
    public function findForJob(string $jobId, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface;

    public function persist(JobLogInterface $jobLog): JobLogInterface;

    public function removeForJob(string $jobId): void;
}
