<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use Carbon\Carbon;
use EonX\EasyBatch\Interfaces\BatchObjectFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectStoreInterface;

abstract class AbstractBatchObjectRepository
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectFactoryInterface
     */
    private $factory;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface
     */
    private $idStrategy;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectStoreInterface
     */
    protected $store;

    public function __construct(
        BatchObjectFactoryInterface $factory,
        BatchObjectIdStrategyInterface $idStrategy,
        BatchObjectStoreInterface $store
    ) {
        $this->factory = $factory;
        $this->idStrategy = $idStrategy;
        $this->store = $store;
    }

    protected function doSave(BatchObjectInterface $batchObject): void
    {
        $batchObjectId = $batchObject->getId() ?? $this->idStrategy->generateId();
        $now = Carbon::now('UTC');

        $batchObject->setId($batchObjectId);
        $batchObject->setCreatedAt($batchObject->getCreatedAt() ?? $now);
        $batchObject->setUpdatedAt($now);

        $this->store->has($batchObjectId) === false
            ? $this->store->persist($batchObject->toArray())
            : $this->store->update($batchObjectId, $batchObject->toArray());
    }

    /**
     * @param int|string $id
     */
    protected function doFind($id): ?BatchObjectInterface
    {
        $data = $this->store->find($id);

        return $data !== null ? $this->factory->createFromArray($data) : null;
    }
}
