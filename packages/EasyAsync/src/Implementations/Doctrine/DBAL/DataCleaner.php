<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Exceptions\UnableToRemoveJobException;
use EonX\EasyAsync\Interfaces\DataCleanerInterface;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class DataCleaner implements DataCleanerInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @var \EonX\EasyAsync\Interfaces\JobLogPersisterInterface
     */
    private $jobLogPersister;

    /**
     * @var \EonX\EasyAsync\Interfaces\JobPersisterInterface
     */
    private $jobPersister;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * DataCleaner constructor.
     *
     * @param \Doctrine\DBAL\Connection $conn
     * @param \EonX\EasyAsync\Interfaces\JobLogPersisterInterface $jobLogPersister
     * @param \EonX\EasyAsync\Interfaces\JobPersisterInterface $jobPersister
     * @param null|\Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Connection $conn,
        JobLogPersisterInterface $jobLogPersister,
        JobPersisterInterface $jobPersister,
        ?LoggerInterface $logger = null
    ) {
        $this->conn = $conn;
        $this->jobLogPersister = $jobLogPersister;
        $this->jobPersister = $jobPersister;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Remove given job and all its job logs.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \EonX\EasyAsync\Exceptions\UnableToRemoveJobException
     */
    public function remove(JobInterface $job): void
    {
        $this->conn->beginTransaction();

        try {
            $this->jobPersister->remove($job);
            $this->jobLogPersister->removeForJob($job->getId());

            $this->conn->commit();
        } catch (\Throwable $throwable) {
            $this->conn->rollBack();
            $this->logger->error($throwable->getMessage());

            throw new UnableToRemoveJobException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
