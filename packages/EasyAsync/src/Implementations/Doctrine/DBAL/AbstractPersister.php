<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Implementations\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\EasyAsyncDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

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
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    protected $randomGenerator;

    public function __construct(
        Connection $conn,
        DateTimeGeneratorInterface $datetime,
        RandomGeneratorInterface $randomGenerator,
        string $table
    ) {
        $this->conn = $conn;
        $this->datetime = $datetime;
        $this->table = $table;
        $this->randomGenerator = $randomGenerator;
    }

    protected function createPaginator(StartSizeDataInterface $startSizeData): DoctrineDbalLengthAwarePaginator
    {
        return new DoctrineDbalLengthAwarePaginator($this->conn, $this->table, $startSizeData);
    }

    protected function doPersist(EasyAsyncDataInterface $data): void
    {
        $data->getId() === null ? $this->insert($data) : $this->update($data);
    }

    protected function getTableForQuery(): string
    {
        return \sprintf('`%s`', $this->table);
    }

    private function getDateTimeNow(): string
    {
        return $this->datetime->now()->format(DateTimeGeneratorInterface::DATE_FORMAT);
    }

    private function insert(EasyAsyncDataInterface $data): void
    {
        $data->setId($this->randomGenerator->uuidV4());

        $params = $data->toArray();
        $now = $this->getDateTimeNow();

        $params['created_at'] = $now;
        $params['updated_at'] = $now;

        $this->conn->insert($this->getTableForQuery(), $params);
    }

    private function update(EasyAsyncDataInterface $data): void
    {
        $params = $data->toArray();
        $params['updated_at'] = $this->getDateTimeNow();

        $this->conn->update($this->getTableForQuery(), $params, ['id' => $data->getId()]);
    }
}
