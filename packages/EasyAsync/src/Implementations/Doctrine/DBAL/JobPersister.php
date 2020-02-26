<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Exceptions\UnableToFindJobException;
use EonX\EasyAsync\Exceptions\UnableToPersistJobException;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;

final class JobPersister extends AbstractPersister implements JobPersisterInterface
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
    public function findOneForUpdate(string $jobId): JobInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :jobId FOR UPDATE', $this->getTableForQuery());

        try {
            $data = $this->conn->fetchAssoc($sql, \compact('jobId'));
        } catch (\Exception $exception) {
            throw new UnableToFindJobException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (\is_array($data)) {
            foreach (['started_at', 'finished_at'] as $datetime) {
                if (empty($data[$datetime])) {
                    continue;
                }

                $data[$datetime] = $this->datetime->fromString($data[$datetime]);
            }

            return Job::fromArray($data);
        }

        throw new UnableToFindJobException(\sprintf('Unable to find job for id "%s"', $jobId));
    }

    /**
     * Persist given job.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobException
     */
    public function persist(JobInterface $job): JobInterface
    {
        try {
            $this->doPersist($job);
        } catch (\Exception $exception) {
            throw new UnableToPersistJobException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $job;
    }
}
