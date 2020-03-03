<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stubs;

use EonX\EasyAsync\Exceptions\UnableToFindJobException;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\ArrayPaginator;

final class JobPersisterStub implements JobPersisterInterface
{
    /**
     * @var string[]
     */
    private $calls = [];

    /**
     * @var null|\EonX\EasyAsync\Interfaces\JobInterface[]
     */
    private $forList;

    /**
     * @var null|\EonX\EasyAsync\Interfaces\JobInterface
     */
    private $forSingle;

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
    public function find(string $jobId): JobInterface
    {
        return $this->returnForSingle(__FUNCTION__);
    }

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
    ): LengthAwarePaginatorInterface {
        return $this->returnForList(__FUNCTION__, $startSizeData);
    }

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
    ): LengthAwarePaginatorInterface {
        return $this->returnForList(__FUNCTION__, $startSizeData);
    }

    /**
     * Find paginated list of jobs for given type.
     *
     * @param string $type
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForType(string $type, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        return $this->returnForList(__FUNCTION__, $startSizeData);
    }

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
    public function findOneForUpdate(string $jobId): JobInterface
    {
        return $this->returnForSingle(__FUNCTION__);
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
     * Persist given job.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobException
     */
    public function persist(JobInterface $job): JobInterface
    {
        $this->calls[] = __FUNCTION__;

        $job->setId('job_id');

        return $job;
    }

    /**
     * Remove given job.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return void
     */
    public function remove(JobInterface $job): void
    {
        $this->calls[] = __FUNCTION__;
    }

    /**
     * Reset persister.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->calls = [];
        $this->forList = null;
        $this->forSingle = null;

        return $this;
    }

    /**
     * Set jobs for list methods.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface[] $jobs
     *
     * @return self
     */
    public function setForList(array $jobs): self
    {
        $this->forList = $jobs;

        return $this;
    }

    /**
     * Set job for single methods.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return self
     */
    public function setForSingle(JobInterface $job): self
    {
        $this->forSingle = $job;

        return $this;
    }

    /**
     * Return list of job for list method.
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

    /**
     * Return job for single method.
     *
     * @param string $method
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToFindJobException
     */
    private function returnForSingle(string $method): JobInterface
    {
        $this->calls[] = $method;

        if ($this->forSingle !== null) {
            return $this->forSingle;
        }

        throw new UnableToFindJobException();
    }
}
