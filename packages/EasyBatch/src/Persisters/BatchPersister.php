<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Persisters;

use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Objects\MessageDecorator;

final class BatchPersister
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BatchItemPersister $batchItemPersister,
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function persistBatch(BatchInterface $batch): BatchInterface
    {
        $batch = $this->batchRepository->save($batch);

        /** @var int|string $batchId */
        $batchId = $batch->getId();
        $totalItems = 0;

        foreach ($batch->getItems() as $item) {
            $totalItems++;

            $item = MessageDecorator::wrap($item);
            $message = $item->getMessage();

            if ($message instanceof BatchInterface) {
                $batchItem = $this->batchItemPersister->persistBatchItem($batchId, $item);
                $message->setApprovalRequired($item->isApprovalRequired());
                $message->setParentBatchItemId($batchItem->getIdOrFail());

                $this->persistBatch($message);

                continue;
            }

            $this->batchItemPersister->persistBatchItem($batchId, $item, $message);
        }

        $batch->setTotal($totalItems);

        return $this->batchRepository->save($batch);
    }
}
