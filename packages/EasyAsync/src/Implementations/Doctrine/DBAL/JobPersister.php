<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Exceptions\UnableToFindJobException;
use EonX\EasyAsync\Exceptions\UnableToPersistJobException;
use EonX\EasyAsync\Helpers\PropertyHelper;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

final class JobPersister extends AbstractPersister implements JobPersisterInterface
{
    /**
     * Find one job for given jobId.
     *
     * @param string $jobId
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToFindJobException
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function find(string $jobId): JobInterface
    {
        return $this->findOneJob($jobId);
    }

    /**
     * Find paginated list of jobs for given target.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForTarget(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface {
        return $this
            ->createPaginator($startSizeData)
            ->setCriteria(static function (QueryBuilder $queryBuilder) use ($target): void {
                $queryBuilder
                    ->where('target_type = :targetType')
                    ->andWhere('target_id = :targetId')
                    ->setParameters(['targetType' => $target->getTargetType(), 'targetId' => $target->getTargetId()]);
            })
            ->setTransformer(function (array $job): JobInterface {
                return $this->newJobFromArray($job);
            });
    }

    /**
     * Find paginated list of jobs for given target type.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForTargetType(
        TargetInterface $target,
        StartSizeDataInterface $startSizeData
    ): LengthAwarePaginatorInterface {
        return $this
            ->createPaginator($startSizeData)
            ->setCriteria(static function (QueryBuilder $queryBuilder) use ($target): void {
                $queryBuilder
                    ->where('target_type = :targetType')
                    ->setParameter('targetType', $target->getTargetType());
            })
            ->setTransformer(function (array $job): JobInterface {
                return $this->newJobFromArray($job);
            });
    }

    /**
     * Find paginated list of jobs for given type.
     *
     * @param string $type
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface
     */
    public function findForType(string $type, StartSizeDataInterface $startSizeData): LengthAwarePaginatorInterface
    {
        return $this
            ->createPaginator($startSizeData)
            ->setCriteria(static function (QueryBuilder $queryBuilder) use ($type): void {
                $queryBuilder
                    ->where('type = :type')
                    ->setParameter('type', $type);
            })
            ->setTransformer(function (array $job): JobInterface {
                return $this->newJobFromArray($job);
            });
    }

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
        return $this->findOneJob($jobId, true);
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

    /**
     * Remove given job.
     *
     * @param \EonX\EasyAsync\Interfaces\JobInterface $job
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function remove(JobInterface $job): void
    {
        $sql = \sprintf('DELETE FROM %s WHERE id = :jobId', $this->getTableForQuery());

        $this->conn->executeQuery($sql, ['jobId' => $job->getId()]);
    }

    /**
     * Find one job for given jobId and forUpdate if set.
     *
     * @param string $jobId
     * @param null|bool $forUpdate
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToFindJobException
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    private function findOneJob(string $jobId, ?bool $forUpdate = null): JobInterface
    {
        $sql = \sprintf(
            'SELECT * FROM %s WHERE id = :jobId%s',
            $this->getTableForQuery(),
            ($forUpdate ?? false) ? ' FOR UPDATE' : ''
        );

        try {
            $data = $this->conn->fetchAssoc($sql, \compact('jobId'));
        } catch (\Exception $exception) {
            throw new UnableToFindJobException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (\is_array($data)) {
            return $this->newJobFromArray($data);
        }

        throw new UnableToFindJobException(\sprintf('Unable to find job for id "%s"', $jobId));
    }

    /**
     * Create new job for given data.
     *
     * @param mixed[] $data
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    private function newJobFromArray(array $data): JobInterface
    {
        $job = Job::fromArray($data);

        PropertyHelper::setDatetimeProperties($job, $data, ['started_at', 'finished_at'], $this->datetime);

        return $job;
    }
}
