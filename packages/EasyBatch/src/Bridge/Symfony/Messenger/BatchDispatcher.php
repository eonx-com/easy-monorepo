<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemClassStamp;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchObjectRequiresApprovalStamp;
use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchStamp;
use EonX\EasyBatch\Exceptions\BatchIdRequiredException;
use EonX\EasyBatch\Interfaces\BatchCancellerInterface;
use EonX\EasyBatch\Interfaces\BatchDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemWithClassInterface;
use EonX\EasyBatch\Interfaces\BatchObjectRequiresApprovalInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchUpdaterInterface;
use EonX\EasyBatch\Objects\AbstractObjectDecorator;
use Symfony\Component\Messenger\MessageBusInterface;

final class BatchDispatcher implements BatchDispatcherInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemFactoryInterface
     */
    private $batchItemFactory;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchUpdaterInterface
     */
    private $batchUpdater;

    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $bus;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchCancellerInterface
     */
    private $canceller;

    public function __construct(
        BatchRepositoryInterface $batchRepository,
        BatchUpdaterInterface $batchUpdater,
        BatchItemFactoryInterface $batchItemFactory,
        BatchItemRepositoryInterface $batchItemRepository,
        MessageBusInterface $bus,
        BatchCancellerInterface $canceller
    ) {
        $this->batchRepository = $batchRepository;
        $this->batchUpdater = $batchUpdater;
        $this->batchItemFactory = $batchItemFactory;
        $this->batchItemRepository = $batchItemRepository;
        $this->bus = $bus;
        $this->canceller = $canceller;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchIdRequiredException
     * @throws \Throwable
     */
    public function dispatch(BatchInterface $batch): void
    {
        // Save batch first, so we're sure it is there before any item is handled
        $batch = $this->batchRepository->save($batch);

        if ($batch->getId() === null) {
            throw new BatchIdRequiredException(\sprintf(
                'Batch is required to have an ID before being dispatched, make sure you set this ID manually,
                 or that your implementation of %s is setting it.',
                BatchRepositoryInterface::class
            ));
        }

        $total = 0;

        try {
            // Dispatch each item to the queue
            foreach ($batch->getItems() as $item) {
                $object = $item instanceof AbstractObjectDecorator ? $item->getObject() : $item;

                $object instanceof BatchInterface
                    ? $this->dispatchChildBatch($batch, $object)
                    : $this->bus->dispatch($object, $this->getStamps($batch, $item));

                $total++;
            }

            // Set total items dispatched on the batch
            $this->batchUpdater->updateTotal($batch, $total);
        } catch (\Throwable $throwable) {
            // If anything happens during dispatch, cancel batch
            $this->canceller->cancel($batch, $throwable);
            $this->batchRepository->save($batch);

            throw $throwable;
        }
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchIdRequiredException
     * @throws \Throwable
     */
    private function dispatchChildBatch(BatchInterface $parentBatch, BatchInterface $childBatch): void
    {
        // Create batchItem "placeholder" for childBatch in parentBatch
        $batchItem = $this->batchItemFactory->create($parentBatch->getId(), \get_class($childBatch));
        $batchItem->setStatus(BatchItemInterface::STATUS_BATCH_PENDING_APPROVAL);

        // Save batchItem "placeholder"
        $batchItem = $this->batchItemRepository->save($batchItem);

        // Associate childBatch with batchItem "placeholder"
        $childBatch->setBatchItemId($batchItem->getId());

        // Dispatch childBatch
        $this->dispatch($childBatch);
    }

    /**
     * @return \Symfony\Component\Messenger\Stamp\StampInterface[]
     */
    private function getStamps(BatchInterface $batch, object $item): array
    {
        $stamps = [new BatchStamp((string)$batch->getId())];

        if ($item instanceof BatchItemWithClassInterface) {
            $stamps[] = new BatchItemClassStamp($item->getClass());
        }

        if ($item instanceof BatchObjectRequiresApprovalInterface) {
            $stamps[] = new BatchObjectRequiresApprovalStamp();
        }

        return $stamps;
    }
}
