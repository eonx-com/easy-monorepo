<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp;
use EonX\EasyBatch\Exceptions\BatchItemInvalidException;
use EonX\EasyBatch\Interfaces\AsyncDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyPagination\Data\StartSizeData;
use Symfony\Component\Messenger\MessageBusInterface;

final class AsyncDispatcher implements AsyncDispatcherInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface
     */
    private $batchItemRepository;

    /**
     * @var \EonX\EasyBatch\Interfaces\BatchRepositoryInterface
     */
    private $batchRepository;

    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private $bus;

    public function __construct(
        BatchRepositoryInterface $batchRepository,
        BatchItemRepositoryInterface $batchItemRepository,
        MessageBusInterface $bus
    ) {
        $this->batchRepository = $batchRepository;
        $this->batchItemRepository = $batchItemRepository;
        $this->bus = $bus;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     */
    public function dispatch(BatchObjectInterface $batchObject): void
    {
        if ($batchObject instanceof BatchInterface) {
            $this->dispatchBatch($batchObject);

            return;
        }

        if ($batchObject instanceof BatchItemInterface) {
            $this->dispatchBatchItem($batchObject);
        }
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     */
    private function dispatchBatch(BatchInterface $batch): void
    {
        /** @var int|string $batchId */
        $batchId = $batch->getId();
        $page = 1;
        $perPage = 15;

        do {
            $startSizeData = new StartSizeData($page, $perPage);
            $paginator = $this->batchItemRepository->findForDispatch($startSizeData, $batchId);

            foreach ($paginator->getItems() as $batchItem) {
                $this->dispatchBatchItem($batchItem);
            }

            $page++;
        } while ($paginator->hasNextPage());
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemInvalidException
     */
    private function dispatchBatchItem(BatchItemInterface $batchItem): void
    {
        /** @var int|string $batchItemId */
        $batchItemId = $batchItem->getId();

        // Simple batchItem with message
        if ($batchItem->getType() === BatchItemInterface::TYPE_MESSAGE) {
            $message = $batchItem->getMessage();

            if ($message === null) {
                throw new BatchItemInvalidException(\sprintf(
                    'BatchItem "%s" is type "%s" but has no message set',
                    $batchItemId,
                    $batchItem->getType()
                ));
            }

            $this->bus->dispatch($message, [new BatchItemStamp($batchItemId)]);

            return;
        }

        // BatchItem for nested batch
        $this->dispatchBatch($this->batchRepository->findNestedOrFail($batchItemId));
    }
}
