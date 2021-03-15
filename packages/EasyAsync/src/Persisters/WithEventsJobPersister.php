<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Persisters;

use EonX\EasyAsync\Events\JobCompletedEvent;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
final class WithEventsJobPersister implements JobPersisterInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\JobPersisterInterface
     */
    private $decorated;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(JobPersisterInterface $decorated, EventDispatcherInterface $eventDispatcher)
    {
        $this->decorated = $decorated;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function find(string $jobId): JobInterface
    {
        return $this->decorated->find($jobId);
    }

    public function findForTarget(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface {
        return $this->decorated->findForTarget($target, $startSizeData);
    }

    public function findForTargetType(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface {
        return $this->decorated->findForTargetType($target, $startSizeData);
    }

    public function findForType(string $type, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        return $this->decorated->findForType($type, $startSizeData);
    }

    public function findOneForUpdate(string $jobId): JobInterface
    {
        return $this->decorated->findOneForUpdate($jobId);
    }

    public function persist(JobInterface $job): JobInterface
    {
        $this->decorated->persist($job);

        if (\in_array($job->getStatus(), [JobInterface::STATUS_COMPLETED, JobInterface::STATUS_FAILED], true)) {
            $this->eventDispatcher->dispatch(new JobCompletedEvent($job));
        }

        return $job;
    }

    public function remove(JobInterface $job): void
    {
        $this->decorated->remove($job);
    }
}
