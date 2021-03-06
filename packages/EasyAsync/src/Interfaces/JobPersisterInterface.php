<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
interface JobPersisterInterface
{
    public function find(string $jobId): JobInterface;

    public function findForTarget(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface;

    public function findForTargetType(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface;

    public function findForType(string $type, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface;

    public function findOneForUpdate(string $jobId): JobInterface;

    public function persist(JobInterface $job): JobInterface;

    public function remove(JobInterface $job): void;
}
