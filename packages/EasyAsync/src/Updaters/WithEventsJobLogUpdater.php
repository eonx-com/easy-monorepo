<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Updaters;

use EonX\EasyAsync\Events\JobLogCompletedEvent;
use EonX\EasyAsync\Events\JobLogFailedEvent;
use EonX\EasyAsync\Events\JobLogInProgressEvent;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\JobLogUpdaterInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class WithEventsJobLogUpdater implements JobLogUpdaterInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\JobLogUpdaterInterface
     */
    private $decorated;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, JobLogUpdaterInterface $decorated)
    {
        $this->dispatcher = $dispatcher;
        $this->decorated = $decorated;
    }

    public function completed(JobLogInterface $jobLog): void
    {
        $this->decorated->completed($jobLog);
        $this->dispatcher->dispatch(new JobLogCompletedEvent($jobLog));
    }

    public function failed(JobLogInterface $jobLog, \Throwable $throwable): void
    {
        $this->decorated->failed($jobLog, $throwable);
        $this->dispatcher->dispatch(new JobLogFailedEvent($jobLog, $throwable));
    }

    public function inProgress(JobLogInterface $jobLog): void
    {
        $this->decorated->inProgress($jobLog);
        $this->dispatcher->dispatch(new JobLogInProgressEvent($jobLog));
    }
}
