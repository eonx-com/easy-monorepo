<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Exceptions\UnableToPersistJobException;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;

final class JobPersister extends AbstractPersister implements JobPersisterInterface
{
    /**
     * @inheritDoc
     */
    public function findOneForUpdate(string $jobId): JobInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :jobId FOR UPDATE', $this->table);

        $data = $this->conn->fetchAssoc($sql, \compact('jobId'));

        // TODO - Handle when job is not found

        return Job::fromArray($data);
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
