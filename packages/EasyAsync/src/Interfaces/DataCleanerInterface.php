<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface DataCleanerInterface
{
    /**
     * Remove given job and all its job logs.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return void
     */
    public function remove(JobInterface $job): void;
}
