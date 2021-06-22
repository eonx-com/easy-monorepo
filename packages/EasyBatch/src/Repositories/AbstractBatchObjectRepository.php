<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Interfaces\BatchObjectFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;

abstract class AbstractBatchObjectRepository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectFactoryInterface
     */
    protected $factory;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface
     */
    private $idStrategy;

    public function __construct(
        BatchObjectFactoryInterface $factory,
        BatchObjectIdStrategyInterface $idStrategy,
        Connection $conn,
        string $table
    ) {
        $this->factory = $factory;
        $this->idStrategy = $idStrategy;
        $this->conn = $conn;
        $this->table = $table;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doSave(BatchObjectInterface $batchObject): void
    {
        $batchObjectId = $batchObject->getId() ?? $this->idStrategy->generateId();
        $now = Carbon::now('UTC');

        $batchObject->setId($batchObjectId);
        $batchObject->setCreatedAt($batchObject->getCreatedAt() ?? $now);
        $batchObject->setUpdatedAt($now);

        $this->has($batchObjectId) === false
            ? $this->conn->insert($this->table, $batchObject->toArray())
            : $this->conn->update($this->table, $batchObject->toArray(), ['id' => $batchObjectId]);
    }

    /**
     * @param int|string $id
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doFind($id): ?BatchObjectInterface
    {
        $data = $this->fetchData($id);

        return $data !== null ? $this->factory->createFromArray($data) : null;
    }

    /**
     * @param int|string $id
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function has($id): bool
    {
        return \is_array($this->fetchData($id));
    }

    /**
     * @param int|string $id
     *
     * @return null|mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchData($id): ?array
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->table);
        $result = $this->conn->fetchAssociative($sql, ['id' => $id]);

        return \is_array($result) ? $result : null;
    }
}
