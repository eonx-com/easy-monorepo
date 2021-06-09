<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Exceptions\Batch\BatchItemStatusInvalidException;
use EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemApproverInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchUpdaterInterface;

final class BatchItemApprover implements BatchItemApproverInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface
     */
    private $batchStore;

    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchUpdaterInterface
     */
    private $batchUpdater;

    public function __construct(BatchStoreInterface $batchStore, BatchUpdaterInterface $batchUpdater)
    {
        $this->batchStore = $batchStore;
        $this->batchUpdater = $batchUpdater;
    }

    /**
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchItemStatusInvalidException
     */
    public function approve(BatchItemInterface $batchItem): BatchItemInterface
    {
        if ($batchItem->getStatus() === BatchItemInterface::STATUS_SUCCESS) {
            return $batchItem;
        }

        if ($batchItem->getStatus() !== BatchItemInterface::STATUS_SUCCESS_PENDING_APPROVAL) {
            throw new BatchItemStatusInvalidException(\sprintf(
                'BatchItem must have status "%s" to be approved, "%s" given',
                BatchItemInterface::STATUS_SUCCESS_PENDING_APPROVAL,
                $batchItem->getStatus()
            ));
        }

        $batchItem->setStatus(BatchItemInterface::STATUS_SUCCESS);

        $this->batchUpdater->updateForItem($this->getBatch($batchItem), $batchItem);

        return $batchItem;
    }

    /**
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException
     */
    private function getBatch(BatchItemInterface $batchItem): BatchInterface
    {
        $batch = $this->batchStore->find($batchItem->getBatchId());

        if ($batch instanceof BatchInterface) {
            return $batch;
        }

        throw new BatchNotFoundException(\sprintf('Batch for id "%s" not found', $batchItem->getBatchId()));
    }
}
