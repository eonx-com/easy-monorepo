<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Events;

use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;
use EonX\EasyAsync\Interfaces\JobInterface;

abstract class AbstractJobEvent implements EasyAsyncEventInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\JobInterface
     */
    private $job;

    /**
     * AbstractJobEvent constructor.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     */
    public function __construct(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * Get job.
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public function getJob(): JobInterface
    {
        return $this->job;
    }
}
