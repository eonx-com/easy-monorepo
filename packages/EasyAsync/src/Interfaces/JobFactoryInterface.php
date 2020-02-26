<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobFactoryInterface
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
    public function create(TargetInterface $target, string $type, ?int $total = null): JobInterface;
}
