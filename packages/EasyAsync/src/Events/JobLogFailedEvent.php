<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Events;

use EonX\EasyAsync\Interfaces\JobLogInterface;
use Throwable;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
final class JobLogFailedEvent extends AbstractJobLogEvent
{
    /**
     * @var \Throwable
     */
    private $throwable;

    public function __construct(JobLogInterface $jobLog, Throwable $throwable)
    {
        parent::__construct($jobLog);

        $this->throwable = $throwable;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
