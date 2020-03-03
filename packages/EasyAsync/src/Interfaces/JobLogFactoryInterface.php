<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobLogFactoryInterface
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
    public function create(TargetInterface $target, string $type, string $jobId): JobLogInterface;
}
