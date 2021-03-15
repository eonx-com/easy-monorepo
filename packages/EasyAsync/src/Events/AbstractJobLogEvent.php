<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Events;

use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
abstract class AbstractJobLogEvent implements EasyAsyncEventInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    private $jobLog;

    public function __construct(JobLogInterface $jobLog)
    {
        $this->jobLog = $jobLog;
    }

    public function getJobLog(): JobLogInterface
    {
        return $this->jobLog;
    }
}
