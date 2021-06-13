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
use EonX\EasyBatch\Interfaces\BatchItemWithClassInterface;
use EonX\EasyBatch\Interfaces\BatchObjectRequiresApprovalInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Objects\AbstractBatchObjectDecorator;
use Symfony\Component\Messenger\MessageBusInterface;

final class BatchDispatcher implements BatchDispatcherInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

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
        MessageBusInterface $bus,
        BatchCancellerInterface $canceller
    ) {
        $this->batchRepository = $batchRepository;
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

        $stamp = new BatchStamp((string)$batch->getId());
        $total = 0;

        try {
            // Dispatch each item to the queue
            foreach ($batch->getItems() as $item) {
                $stamps = [$stamp];

                if ($item instanceof BatchItemWithClassInterface) {
                    $stamps[] = new BatchItemClassStamp($item->getClass());
                }

                if ($item instanceof BatchObjectRequiresApprovalInterface) {
                    $stamps[] = new BatchObjectRequiresApprovalStamp();
                }

                $this->bus->dispatch(
                    $item instanceof AbstractBatchObjectDecorator ? $item->getBatchObject() : $item,
                    $stamps
                );

                $total++;
            }

            // Set total items dispatched on the batch
            $batch->setTotal($total);
        } catch (\Throwable $throwable) {
            // If anything happens during dispatch, cancel batch
            $this->canceller->cancel($batch, $throwable);

            throw $throwable;
        } finally {
            $this->batchRepository->save($batch);
        }
    }
}
