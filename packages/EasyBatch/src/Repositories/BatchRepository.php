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

    public function find($id): ?BatchInterface
    {
        /** @var null|\EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->doFind($id);

        return $batch;
    }

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

        $this->store->startUpdate();

        try {
            /** @var \EonX\EasyBatch\Interfaces\BatchInterface $freshBatch */
            $freshBatch = $func($this->store->findForUpdate($batch->getId()));

            $this->store->update($freshBatch->getId(), $freshBatch->toArray());
            $this->store->finishUpdate();

            return $freshBatch;
        } catch (\Throwable $throwable) {
            $this->store->cancelUpdate();

            throw $throwable;
        }
    }
}
