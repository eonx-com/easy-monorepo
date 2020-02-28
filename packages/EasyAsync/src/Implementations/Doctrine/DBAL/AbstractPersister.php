<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\EasyAsyncDataInterface;
use EonX\EasyAsync\Interfaces\UuidGeneratorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator;

abstract class AbstractPersister
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var \EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface
     */
    protected $datetime;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var \EonX\EasyAsync\Interfaces\UuidGeneratorInterface
     */
    protected $uuid;

    /**
     * AbstractPersister constructor.
     *
     * @param \Doctrine\DBAL\Connection $conn
     * @param \EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface $datetime
     * @param \EonX\EasyAsync\Interfaces\UuidGeneratorInterface $uuid
     * @param string $table
     */
    public function __construct(
        Connection $conn,
        DateTimeGeneratorInterface $datetime,
        UuidGeneratorInterface $uuid,
        string $table
    ) {
        $this->conn = $conn;
        $this->datetime = $datetime;
        $this->table = $table;
        $this->uuid = $uuid;
    }

    /**
     * Create paginator.
     *
     * @param \EonX\EasyPagination\Interfaces\StartSizeDataInterface $startSizeData
     *
     * @return \EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator
     */
    protected function createPaginator(StartSizeDataInterface $startSizeData): DoctrineDbalLengthAwarePaginator
    {
        return new DoctrineDbalLengthAwarePaginator($this->conn, $this->table, $startSizeData);
    }

    /**
     * Persist given data in given table, handle insert and update.
     *
     * @param \EonX\EasyAsync\Interfaces\EasyAsyncDataInterface $data
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateUuidException
     * @throws \Exception
     */
    protected function doPersist(EasyAsyncDataInterface $data): void
    {
        $data->getId() === null ? $this->insert($data) : $this->update($data);
    }

    /**
     * Get table name for query.
     *
     * @return string
     */
    protected function getTableForQuery(): string
    {
        return \sprintf('`%s`', $this->table);
    }

    /**
     * Get now.
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getDateTimeNow(): string
    {
        return $this->datetime->now()->format(DateTimeGeneratorInterface::DATE_FORMAT);
    }

    /**
     * Insert given data.
     *
     * @param \EonX\EasyAsync\Interfaces\EasyAsyncDataInterface $data
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateUuidException
     * @throws \Exception
     */
    private function insert(EasyAsyncDataInterface $data): void
    {
        $data->setId($this->uuid->generate());

        $params = $data->toArray();
        $now = $this->getDateTimeNow();

        $params['created_at'] = $now;
        $params['updated_at'] = $now;

        $this->conn->insert($this->getTableForQuery(), $params);
    }

    /**
     * Update given data.
     *
     * @param \EonX\EasyAsync\Interfaces\EasyAsyncDataInterface $data
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    private function update(EasyAsyncDataInterface $data): void
    {
        $params = $data->toArray();
        $params['updated_at'] = $this->getDateTimeNow();

        $this->conn->update($this->getTableForQuery(), $params, ['id' => $data->getId()]);
    }
}
