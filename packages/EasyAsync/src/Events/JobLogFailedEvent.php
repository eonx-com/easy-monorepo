<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Events;

use EonX\EasyAsync\Interfaces\JobLogInterface;
use Throwable;

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
