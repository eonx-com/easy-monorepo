<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Persisters;

use EonX\EasyAsync\Events\JobCompletedEvent;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

final class WithEventsJobPersister implements JobPersisterInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\JobPersisterInterface
     */
    private $decorated;

    /**
     * @var \EonX\EasyAsync\Interfaces\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * WithEventsJobPersister constructor.
     *
     * @param \EonX\EasyAsync\Interfaces\JobPersisterInterface $decorated
     * @param \EonX\EasyAsync\Interfaces\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(JobPersisterInterface $decorated, EventDispatcherInterface $eventDispatcher)
    {
        $this->decorated = $decorated;
        $this->eventDispatcher = $eventDispatcher;
    }

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
        return $this->decorated->find($jobId);
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
        return $this->decorated->findForTarget($target, $startSizeData);
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
        return $this->decorated->findForTargetType($target, $startSizeData);
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
        return $this->decorated->findForType($type, $startSizeData);
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
        return $this->decorated->findOneForUpdate($jobId);
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
        $this->decorated->persist($job);

        if (\in_array($job->getStatus(), [JobInterface::STATUS_COMPLETED, JobInterface::STATUS_FAILED], true)) {
            $this->eventDispatcher->dispatch(new JobCompletedEvent($job));
        }

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
        $this->decorated->remove($job);
    }
}
