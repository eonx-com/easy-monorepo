<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Persister;

use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchInterface;
use EonX\EasyBatch\Common\ValueObject\MessageWrapper;

final class BatchPersister
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BatchItemPersister $batchItemPersister,
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function persistBatch(BatchInterface $batch): BatchInterface
    {
        $batch = $this->batchRepository->save($batch);

        /** @var int|string $batchId */
        $batchId = $batch->getId();
        $totalItems = 0;

        foreach ($batch->getItems() as $item) {
            $totalItems++;

            $item = MessageWrapper::wrap($item);
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
