<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Factories;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Interfaces\JobLogFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

final class JobLogFactory implements JobLogFactoryInterface
{
    public function create(TargetInterface $target, string $type, string $jobId): JobLogInterface
    {
        return new JobLog($target, $type, $jobId);
    }
}
