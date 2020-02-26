<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobLogUpdaterInterface
{
    /**
     * Update given jobLog to completed.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function completed(JobLogInterface $jobLog): void;

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
    public function failed(JobLogInterface $jobLog, \Throwable $throwable): void;

    /**
     * Update given jobLog to in progress.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function inProgress(JobLogInterface $jobLog): void;
}
