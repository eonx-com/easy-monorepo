<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stubs;

use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\ArrayPaginator;

final class JobLogPersisterStub implements JobLogPersisterInterface
{
    /**
     * @var string[]
     */
    private $calls = [];

    /**
     * @var null|\EonX\EasyAsync\Interfaces\JobLogInterface[]
     */
    private $forList;

    public function findForJob(string $jobId, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        return $this->returnForList(__FUNCTION__, $startSizeData);
    }

    /**
     * @return string[]
     */
    public function getMethodCalls(): array
    {
        return $this->calls;
    }

    public function persist(JobLogInterface $jobLog): JobLogInterface
    {
        $this->calls[] = __FUNCTION__;

        $jobLog->setId('job_log_id');

        return $jobLog;
    }

    public function removeForJob(string $jobId): void
    {
        $this->calls[] = __FUNCTION__;
    }

    private function returnForList(string $method, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        $this->calls[] = $method;

        $list = $this->forList ?? [];

        return new ArrayPaginator($list, \count($list), $startSizeData);
    }
}
