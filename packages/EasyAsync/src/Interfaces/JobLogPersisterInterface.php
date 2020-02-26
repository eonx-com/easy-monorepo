<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobLogPersisterInterface
{
    /**
     * Persist given job log.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobLogException
     */
    public function persist(JobLogInterface $jobLog): JobLogInterface;
}
