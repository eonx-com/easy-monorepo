<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Factories;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Interfaces\JobLogFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

final class JobLogFactory implements JobLogFactoryInterface
{
    /**
     * Create job log for given target, type and job id.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param string $type
     * @param string $jobId
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function create(TargetInterface $target, string $type, string $jobId): JobLogInterface
    {
        return new JobLog($target, $type, $jobId);
    }
}
