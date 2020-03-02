<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Factories;

use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Interfaces\JobFactoryInterface;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

final class JobFactory implements JobFactoryInterface
{
    /**
     * Create job for given target and total of job logs to process.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param string $type
     * @param null|int $total Will default to 1
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public function create(TargetInterface $target, string $type, ?int $total = null): JobInterface
    {
        return new Job($target, $type, $total);
    }
}
