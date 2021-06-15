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
        $now = Carbon::now('UTC');

        $batchObject->setId($batchObject->getId() ?? $this->idStrategy->generateId());
        $batchObject->setCreatedAt($batchObject->getCreatedAt() ?? $now);
        $batchObject->setUpdatedAt($now);

        $this->store->has($batchObject->getId()) === false
            ? $this->store->persist($batchObject->toArray())
            : $this->store->update($batchObject->getId(), $batchObject->toArray());
    }

    protected function doFind($id): ?BatchObjectInterface
    {
        $data = $this->store->find($id);

        return $data !== null ? $this->factory->createFromArray($data) : null;
    }
}
