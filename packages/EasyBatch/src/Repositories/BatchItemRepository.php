<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemStoreInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;

final class BatchItemRepository extends AbstractBatchObjectRepository implements BatchItemRepositoryInterface
{
    public function __construct(
        BatchItemFactoryInterface $factory,
        BatchObjectIdStrategyInterface $idStrategy,
        BatchItemStoreInterface $store
    ) {
        parent::__construct($factory, $idStrategy, $store);
    }

    public function save(BatchItemInterface $batchItem): BatchItemInterface
    {
        $this->doSave($batchItem);

        return $batchItem;
    }

    public function find($id): ?BatchItemInterface
    {
        /** @var null|\EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->doFind($id);

        return $batchItem;
    }
}
