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

    public function remove(JobInterface $job): void
    {
        $this->conn->beginTransaction();

        try {
            $this->jobPersister->remove($job);
            $this->jobLogPersister->removeForJob((string)$job->getId());

            $this->conn->commit();
        } catch (\Throwable $throwable) {
            $this->conn->rollBack();
            $this->logger->error($throwable->getMessage());

            throw new UnableToRemoveJobException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
