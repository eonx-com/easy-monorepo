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

    /**
     * Find paginated list of job logs for given job.
     *
     * @param string $jobId
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForJob(string $jobId, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        return $this->returnForList(__FUNCTION__, $startSizeData);
    }

    /**
     * Get method calls.
     *
     * @return string[]
     */
    public function getMethodCalls(): array
    {
        return $this->calls;
    }

    /**
     * Persist given job log.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobLogException
     */
    public function persist(JobLogInterface $jobLog): JobLogInterface
    {
        $this->calls[] = __FUNCTION__;

        $jobLog->setId('job_log_id');

        return $jobLog;
    }

    /**
     * Remove all job logs for given job.
     *
     * @param string $jobId
     *
     * @return void
     */
    public function removeForJob(string $jobId): void
    {
        $this->calls[] = __FUNCTION__;
    }

    /**
     * Return list of job logs for list method.
     *
     * @param string $method
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    private function returnForList(string $method, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        $this->calls[] = $method;

        $list = $this->forList ?? [];

        return new ArrayPaginator($list, \count($list), $startSizeData);
    }
}
