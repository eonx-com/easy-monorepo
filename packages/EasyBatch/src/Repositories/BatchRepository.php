<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use EonX\EasyBatch\Exceptions\BatchIdRequiredException;
use EonX\EasyBatch\Exceptions\BatchNotFoundException;
use EonX\EasyBatch\Interfaces\BatchFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchStoreInterface;

final class BatchRepository extends AbstractBatchObjectRepository implements BatchRepositoryInterface
{
    public function __construct(
        BatchFactoryInterface $factory,
        BatchObjectIdStrategyInterface $idStrategy,
        BatchStoreInterface $store
    ) {
        parent::__construct($factory, $idStrategy, $store);
    }

    public function save(BatchInterface $batch): BatchInterface
    {
        $this->doSave($batch);

        return $batch;
    }

    /**
     * @param int|string $id
     */
    public function find($id): ?BatchInterface
    {
        /** @var null|\EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->doFind($id);

        return $batch;
    }

    /**
     * @param int|string $id
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    public function findOrFail($id): BatchInterface
    {
        $batch = $this->find($id);

        if ($batch !== null) {
            return $batch;
        }

        throw new BatchNotFoundException(\sprintf('Batch for id "%s" not found', $id));
    }

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchInterface $batch
     * @param callable $func
     *
     * @return \EonX\EasyBatch\Interfaces\BatchInterface
     * @throws \EonX\EasyBatch\Exceptions\BatchIdRequiredException
     * @throws \Throwable
     */
    public function updateAtomic(BatchInterface $batch, callable $func): BatchInterface
    {
        if ($batch->getId() === null) {
            throw new BatchIdRequiredException('Batch ID is required to update it.');
        }

        /** @var \EonX\EasyBatch\Interfaces\BatchStoreInterface $store */
        $store = $this->store;

        $store->startUpdate();

        try {
            $store->lockForUpdate($batch->getId());

            /** @var \EonX\EasyBatch\Interfaces\BatchInterface $freshBatch */
            $freshBatch = $func($batch);

            if ($freshBatch->getId() === null) {
                throw new BatchIdRequiredException('Batch ID is required to update it.');
            }

            $store->update($freshBatch->getId(), $freshBatch->toArray());
            $store->finishUpdate();

            return $freshBatch;
        } catch (\Throwable $throwable) {
            $store->cancelUpdate();

            throw $throwable;
        }
    }
}
