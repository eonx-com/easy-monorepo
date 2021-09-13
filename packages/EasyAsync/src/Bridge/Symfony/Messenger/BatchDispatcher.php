<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Batch\BatchItemRequiresApprovalDecorator;
use EonX\EasyAsync\Exceptions\Batch\BatchIdRequiredException;
use EonX\EasyAsync\Interfaces\Batch\BatchCancellerInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchDispatcherInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemRequiresApprovalInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
final class BatchDispatcher implements BatchDispatcherInterface
{
    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $bus;

    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchCancellerInterface
     */
    private $canceller;

    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface
     */
    private $store;

    public function __construct(
        MessageBusInterface $bus,
        BatchCancellerInterface $canceller,
        BatchStoreInterface $store
    ) {
        $this->bus = $bus;
        $this->canceller = $canceller;
        $this->store = $store;
    }

    /**
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchIdRequiredException
     * @throws \Throwable
     */
    public function dispatch(BatchInterface $batch): void
    {
        // Store batch first, so we're sure it is there before any item is handled
        $batch = $this->store->store($batch);

        if ($batch->getId() === null) {
            throw new BatchIdRequiredException(\sprintf(
                'Batch is required to have an ID before being dispatched, make sure you set this ID manually,
                 or that your implementation of %s is setting it.',
                BatchStoreInterface::class
            ));
        }

        $stamp = new BatchStamp((string)$batch->getId());
        $total = 0;

        try {
            // Dispatch each item to the queue
            foreach ($batch->getItems() as $item) {
                $stamps = [$stamp];

                if ($item instanceof BatchItemRequiresApprovalInterface) {
                    $stamps[] = new BatchItemRequiresApprovalStamp();
                }

                if ($item instanceof BatchItemRequiresApprovalDecorator) {
                    $item = $item->getItem();
                }

                $this->bus->dispatch($item, $stamps);

                $total++;
            }

            // Set total items dispatched on the batch
            $batch->setTotal($total);
        } catch (\Throwable $throwable) {
            // If anything happens during dispatch, cancel batch
            $this->canceller->cancel($batch, $throwable);

            throw $throwable;
        } finally {
            $this->store->store($batch);
        }
    }
}
