<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Updaters;

use EonX\EasyAsync\Events\JobLogCompletedEvent;
use EonX\EasyAsync\Events\JobLogFailedEvent;
use EonX\EasyAsync\Events\JobLogInProgressEvent;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\JobLogUpdaterInterface;

final class WithEventsJobLogUpdater implements JobLogUpdaterInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\JobLogUpdaterInterface
     */
    private $decorated;

    /**
     * @var \EonX\EasyAsync\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * WithEventsJobLogUpdater constructor.
     *
     * @param \EonX\EasyAsync\Interfaces\EventDispatcherInterface $dispatcher
     * @param \EonX\EasyAsync\Interfaces\JobLogUpdaterInterface $decorated
     */
    public function __construct(EventDispatcherInterface $dispatcher, JobLogUpdaterInterface $decorated)
    {
        $this->dispatcher = $dispatcher;
        $this->decorated = $decorated;
    }

    /**
     * Update given jobLog to completed.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function completed(JobLogInterface $jobLog): void
    {
        $this->decorated->completed($jobLog);
        $this->dispatcher->dispatch(new JobLogCompletedEvent($jobLog));
    }

    /**
     * Update given jobLog to failed for given throwable.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     * @param \Throwable $throwable
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function failed(JobLogInterface $jobLog, \Throwable $throwable): void
    {
        $this->decorated->failed($jobLog, $throwable);
        $this->dispatcher->dispatch(new JobLogFailedEvent($jobLog, $throwable));
    }

    /**
     * Update given jobLog to in progress.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function inProgress(JobLogInterface $jobLog): void
    {
        $this->decorated->inProgress($jobLog);
        $this->dispatcher->dispatch(new JobLogInProgressEvent($jobLog));
    }
}
