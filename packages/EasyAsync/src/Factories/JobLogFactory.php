<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Factories;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Interfaces\JobLogFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
final class JobLogFactory implements JobLogFactoryInterface
{
    public function create(TargetInterface $target, string $type, string $jobId): JobLogInterface
    {
        return new JobLog($target, $type, $jobId);
    }
}
