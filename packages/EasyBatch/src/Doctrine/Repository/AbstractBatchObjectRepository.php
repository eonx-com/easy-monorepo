<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Doctrine\Repository;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Common\Exception\BatchObjectNotSavedException;
use EonX\EasyBatch\Common\Factory\BatchObjectFactoryInterface;
use EonX\EasyBatch\Common\Strategy\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Common\Transformer\BatchObjectTransformerInterface;
use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;

abstract class AbstractBatchObjectRepository
{
    /**
     * @var string[]|null
     */
    private ?array $tableColumns = null;

    public function __construct(
        protected BatchObjectFactoryInterface $factory,
        protected BatchObjectIdStrategyInterface $idStrategy,
        protected BatchObjectTransformerInterface $transformer,
        protected Connection $connection,
        protected string $table,
    ) {
        // No body needed
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doFind(int|string $id): ?BatchObjectInterface
    {
        $data = $this->fetchData($id);

        return $data !== null ? $this->factory->createFromArray($data) : null;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectNotSavedException
     */
    protected function doSave(BatchObjectInterface $batchObject): void
    {
        $batchObjectId = $batchObject->getId() ?? $this->idStrategy->generateId();
        $now = Carbon::now('UTC');

        $batchObject->setId($batchObjectId);
        $batchObject->setCreatedAt($batchObject->getCreatedAt() ?? $now);
        $batchObject->setUpdatedAt($now);

        $data = $this->transformer->transformToArray($batchObject);
        foreach (\array_diff(\array_keys($data), $this->resolveTableColumns()) as $toRemove) {
            unset($data[$toRemove]);
        }

        $batchObjectExists = $this->has($batchObjectId);
        $affectedRows = (int)($batchObjectExists === false
            ? $this->connection->insert($this->table, $data)
            : $this->connection->update($this->table, $data, ['id' => $batchObjectId]));

        // This logic should affect only one row, otherwise something went wrong
        if ($affectedRows !== 1) {
            throw new BatchObjectNotSavedException(\sprintf(
                '%s with id %s was not %s in database',
                $batchObject::class,
                $batchObjectId,
                $batchObjectExists ? 'updated' : 'inserted'
            ));
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function has(int|string $id): bool
    {
        return \is_array($this->fetchData($id));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchData(int|string $id): ?array
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->table);
        $result = $this->connection->fetchAssociative($sql, ['id' => $id]);

        return \is_array($result) ? $result : null;
    }

    private function resolveTableColumns(): array
    {
        return $this->tableColumns
            ?? ($this->tableColumns = \array_keys($this->connection->createSchemaManager()->listTableColumns($this->table)));
    }
}
