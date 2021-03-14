<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
interface JobLogUpdaterInterface
{
    public function completed(JobLogInterface $jobLog): void;

    public function failed(JobLogInterface $jobLog, \Throwable $throwable): void;

    public function inProgress(JobLogInterface $jobLog): void;
}
