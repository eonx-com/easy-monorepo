<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Exceptions\UnableToPersistJobLogException;
use EonX\EasyAsync\Helpers\PropertyHelper;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
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

    public function __construct(
        Connection $conn,
        DateTimeGeneratorInterface $dateTime,
        UuidV4GeneratorInterface $uuid,
        JobPersisterInterface $jobPersister,
        LoggerInterface $logger,
        string $table
    ) {
        parent::__construct($conn, $dateTime, $uuid, $table);

        $this->jobPersister = $jobPersister;
        $this->logger = $logger;
    }

    public function findForJob(string $jobId, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        return $this
            ->createPaginator($startSizeData)
            ->setCriteria(static function (QueryBuilder $queryBuilder) use ($jobId): void {
                $queryBuilder
                    ->where('job_id = :jobId')
                    ->setParameter('jobId', $jobId);
            })
            ->setTransformer(function (array $data): JobLogInterface {
                $jobLog = JobLog::fromArray($data);

                PropertyHelper::setDatetimeProperties($jobLog, $data, ['started_at', 'finished_at'], $this->datetime);

                return $jobLog;
            });
    }

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

    public function removeForJob(string $jobId): void
    {
        $sql = \sprintf('DELETE FROM %s WHERE job_id = :jobId', $this->getTableForQuery());

        $this->conn->executeQuery($sql, ['jobId' => $jobId]);
    }

    private function updateJob(JobLogInterface $jobLog, JobInterface $job): JobInterface
    {
        switch ($jobLog->getStatus()) {
            case JobLogInterface::STATUS_FAILED:
                $job->setProcessed($job->getProcessed() + 1);
                $job->setFailed($job->getFailed() + 1);
                break;
            case JobLogInterface::STATUS_COMPLETED:
                $job->setProcessed($job->getProcessed() + 1);
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
            $job->setFinishedAt($jobLog->getFinishedAt());
        }

        return $job;
    }
}
