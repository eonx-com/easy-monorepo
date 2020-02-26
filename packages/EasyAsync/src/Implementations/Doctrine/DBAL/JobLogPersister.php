<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Exceptions\UnableToPersistJobLogException;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Interfaces\UuidGeneratorInterface;
use Psr\Log\LoggerInterface;

final class JobLogPersister extends AbstractPersister implements JobLogPersisterInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\JobPersisterInterface
     */
    private $jobPersister;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * JobLogPersister constructor.
     *
     * @param \Doctrine\DBAL\Connection $conn
     * @param \EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface $dateTime
     * @param \EonX\EasyAsync\Interfaces\UuidGeneratorInterface $uuid
     * @param \EonX\EasyAsync\Interfaces\JobPersisterInterface $jobPersister
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $table
     */
    public function __construct(
        Connection $conn,
        DateTimeGeneratorInterface $dateTime,
        UuidGeneratorInterface $uuid,
        JobPersisterInterface $jobPersister,
        LoggerInterface $logger,
        string $table
    ) {
        parent::__construct($conn, $dateTime, $uuid, $table);

        $this->jobPersister = $jobPersister;
        $this->logger = $logger;
    }

    /**
     * Persist given job log.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobLogException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function persist(JobLogInterface $jobLog): JobLogInterface
    {
        $this->conn->beginTransaction();

        try {
            $job = $this->jobPersister->findOneForUpdate($jobLog->getJobId());

            $this->jobPersister->persist($this->updateJob($jobLog, $job));
            $this->doPersist($jobLog);

            $this->conn->commit();
        } catch (\Throwable $throwable) {
            $this->conn->rollBack();
            $this->logger->error($throwable->getMessage());

            if ($throwable instanceof UnableToPersistJobLogException) {
                throw $throwable;
            }
        }

        return $jobLog;
    }

    /**
     * Update given job for given job log.
     *
     * @param \EonX\EasyAsync\Interfaces\JobLogInterface $jobLog
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    private function updateJob(JobLogInterface $jobLog, JobInterface $job): JobInterface
    {
        $job->setProcessed($job->getProcessed() + 1);

        switch ($jobLog->getStatus()) {
            case JobLogInterface::STATUS_FAILED:
                $job->setFailed($job->getFailed() + 1);
                break;
            case JobLogInterface::STATUS_COMPLETED:
                $job->setSucceeded($job->getSucceeded() + 1);
                break;
        }

        // If first job log
        if ($job->getStartedAt() === null) {
            $job->setStartedAt($jobLog->getStartedAt());
            $job->setStatus(JobInterface::STATUS_IN_PROGRESS);
        }

        // Job finished
        if ($job->getTotal() === $job->getProcessed()) {
            $job->setStatus($job->getFailed() > 0 ? JobInterface::STATUS_FAILED : JobInterface::STATUS_COMPLETED);
            $job->setFinishedAt($this->datetime->now());
        }

        return $job;
    }
}
