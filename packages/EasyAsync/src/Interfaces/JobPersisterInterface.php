<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobPersisterInterface
{
    /**
     * Find one job for given jobId and lock it for update.
     *
     * @param string $jobId
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToFindJobException
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function findOneForUpdate(string $jobId): JobInterface;

    /**
     * Persist given job.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobException
     */
    public function persist(JobInterface $job): JobInterface;
}
