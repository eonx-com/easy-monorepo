<?php

declare(strict_types=1);

namespace EonX\EasyBatch;

use EonX\EasyBatch\Events\BatchObjectApprovedEvent;
use EonX\EasyBatch\Exceptions\BatchItemStatusInvalidException;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectApproverInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchObjectApprover implements BatchObjectApproverInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        BatchRepositoryInterface $batchRepository,
        BatchItemRepositoryInterface $batchItemRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->batchRepository = $batchRepository;
        $this->batchItemRepository = $batchItemRepository;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchItemStatusInvalidException
     */
    public function approve(BatchObjectInterface $batchObject): BatchObjectInterface
    {
        if ($batchObject->getStatus() === BatchObjectInterface::STATUS_SUCCESS) {
            return $batchObject;
        }

        if ($batchObject->getStatus() !== BatchObjectInterface::STATUS_SUCCESS_PENDING_APPROVAL) {
            throw new BatchItemStatusInvalidException(\sprintf(
                'BatchItem must have status "%s" to be approved, "%s" given',
                BatchItemInterface::STATUS_SUCCESS_PENDING_APPROVAL,
                $batchObject->getStatus()
            ));
        }

        $batchObject->setStatus(BatchObjectInterface::STATUS_SUCCESS);

        if ($batchObject instanceof BatchInterface) {
            $this->batchRepository->save($batchObject);
        }

        if ($batchObject instanceof BatchItemInterface) {
            $this->batchItemRepository->save($batchObject);
        }

        $this->dispatcher->dispatch(new BatchObjectApprovedEvent($batchObject));

        return $batchObject;
    }
}
