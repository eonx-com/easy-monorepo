<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Events;

use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;

abstract class AbstractJobLogEvent implements EasyAsyncEventInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    private $jobLog;

    /**
     * JobLogCompletedEvent constructor.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     */
    public function __construct(JobLogInterface $jobLog)
    {
        $this->jobLog = $jobLog;
    }

    /**
     * Get job log.
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function getJobLog(): JobLogInterface
    {
        return $this->jobLog;
    }
}
