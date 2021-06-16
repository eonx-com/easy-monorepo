<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Listeners;

use EonX\EasyBatch\Events\BatchCompletedEvent;
use EonX\EasyBatch\Events\BatchObjectApprovedEvent;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchUpdaterInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

final class BatchObjectApprovedListener
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchUpdaterInterface
     */
    private $batchUpdater;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        BatchRepositoryInterface $batchRepository,
        BatchUpdaterInterface $batchUpdater,
        EventDispatcherInterface $dispatcher
    ) {
        $this->batchRepository = $batchRepository;
        $this->batchUpdater = $batchUpdater;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    public function __invoke(BatchObjectApprovedEvent $event): void
    {
        $batchObject = $event->getBatchObject();

        // If batch, dispatch completed event
        if ($batchObject instanceof BatchInterface) {
            $this->dispatcher->dispatch(new BatchCompletedEvent($batchObject));

            return;
        }

        // If batchItem, update batch for item
        if ($batchObject instanceof BatchItemInterface) {
            $this->batchUpdater->updateForItem(
                $this->batchRepository->findOrFail($batchObject->getBatchId()),
                $batchObject
            );
        }
    }
}
