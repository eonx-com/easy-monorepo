<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
interface WithProcessJobLogDataInterface
{
    public function getProcessJobLogData(): ProcessJobLogDataInterface;

    public function setJobId(string $jobId): void;

    public function setTarget(TargetInterface $target): void;

    public function setType(string $type): void;
}
