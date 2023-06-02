<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Persisters;

use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Objects\MessageDecorator;

final class BatchItemPersister
{
    public function __construct(
        private readonly BatchItemFactoryInterface $batchItemFactory,
        private readonly BatchItemRepositoryInterface $batchItemRepository,
    ) {
    }

    public function persistBatchItem(
        int|string $batchId,
        MessageDecorator $item,
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
