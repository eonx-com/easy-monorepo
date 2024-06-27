<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Persister;

use EonX\EasyBatch\Common\Factory\BatchItemFactoryInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
use EonX\EasyBatch\Common\ValueObject\MessageWrapper;

final class BatchItemPersister
{
    public function __construct(
        private readonly BatchItemFactoryInterface $batchItemFactory,
        private readonly BatchItemRepositoryInterface $batchItemRepository,
    ) {
    }

    public function persistBatchItem(
        int|string $batchId,
        MessageWrapper $item,
        ?object $message = null,
    ): BatchItemInterface {
        $batchItem = $this->batchItemFactory->create($batchId, $message, $item->getClass());

        $batchItem->setApprovalRequired($item->isApprovalRequired());

        $batchItem
            ->setEncrypted($item->isEncrypted())
            ->setMaxAttempts($item->getMaxAttempts());

        if ($item->getDependsOn() !== null) {
            $batchItem->setDependsOnName($item->getDependsOn());
        }

        if ($item->getEncryptionKeyName() !== null) {
            $batchItem->setEncryptionKeyName($item->getEncryptionKeyName());
        }

        if ($item->getMetadata() !== null) {
            $batchItem->setMetadata($item->getMetadata());
        }

        if ($item->getName() !== null) {
            $batchItem->setName($item->getName());
        }

        return $this->batchItemRepository->save($batchItem);
    }
}
