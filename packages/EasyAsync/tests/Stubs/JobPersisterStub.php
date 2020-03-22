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

    public function find(string $jobId): JobInterface
    {
        return $this->returnForSingle(__FUNCTION__);
    }

    public function findForTarget(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface {
        return $this->returnForList(__FUNCTION__, $startSizeData);
    }

    public function findForTargetType(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface {
        return $this->returnForList(__FUNCTION__, $startSizeData);
    }

    public function findForType(string $type, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        return $this->returnForList(__FUNCTION__, $startSizeData);
    }

    public function findOneForUpdate(string $jobId): JobInterface
    {
        return $this->returnForSingle(__FUNCTION__);
    }

    /**
     * @return string[]
     */
    public function getMethodCalls(): array
    {
        return $this->calls;
    }

    public function persist(JobInterface $job): JobInterface
    {
        $this->calls[] = __FUNCTION__;

        $job->setId('job_id');

        return $job;
    }

    public function remove(JobInterface $job): void
    {
        $this->calls[] = __FUNCTION__;
    }

    public function reset(): self
    {
        $this->calls = [];
        $this->forList = null;
        $this->forSingle = null;

        return $this;
    }

    /**
     * @param \EonX\EasyAsync\Interfaces\JobInterface[] $jobs
     */
    public function setForList(array $jobs): self
    {
        $this->forList = $jobs;

        return $this;
    }

    public function setForSingle(JobInterface $job): self
    {
        $this->forSingle = $job;

        return $this;
    }

    private function returnForList(string $method, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        $this->calls[] = $method;

        $list = $this->forList ?? [];

        return new ArrayPaginator($list, \count($list), $startSizeData);
    }

    private function returnForSingle(string $method): JobInterface
    {
        $this->calls[] = $method;

        if ($this->forSingle !== null) {
            return $this->forSingle;
        }

        throw new UnableToFindJobException();
    }
}
