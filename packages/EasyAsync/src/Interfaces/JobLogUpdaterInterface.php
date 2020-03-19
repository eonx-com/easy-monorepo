<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobLogUpdaterInterface
{
    public function completed(JobLogInterface $jobLog): void;

    public function failed(JobLogInterface $jobLog, \Throwable $throwable): void;

    public function inProgress(JobLogInterface $jobLog): void;
}
