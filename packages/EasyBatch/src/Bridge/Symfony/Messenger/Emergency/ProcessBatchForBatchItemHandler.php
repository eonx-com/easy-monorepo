<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Emergency;

use Carbon\Carbon;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Processors\BatchProcessor;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ProcessBatchForBatchItemHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly BatchObjectManagerInterface $batchObjectManager,
        private readonly BatchProcessor $batchProcessor,
        private readonly BatchRepositoryInterface $batchRepository
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function __invoke(ProcessBatchForBatchItemMessage $message): void
    {
        $batchItem = $this->batchItemRepository->findOrFail($message->getBatchItemId());
        $batch = $this->batchRepository->findOrFail($batchItem->getBatchId());

        // Update batch metadata to reflect emergency flow triggered
        $updateFreshBatch = static function (BatchInterface $freshBatch) use ($message): void {
            $metadata = $freshBatch->getMetadata() ?? [];
            $internal = $metadata['_internal'] ?? [];
            $now = Carbon::now('UTC')->format(BatchObjectInterface::DATETIME_FORMAT);

            if (isset($internal['process_batch_emergency']) === false) {
                $internal['process_batch_emergency'] = [];
            }

            $internal['process_batch_emergency'][] = [
                'triggered_at' => $now,
                'error_details' => $message->getErrorDetails(),
            ];

            $metadata['_internal'] = $internal;

            $freshBatch->setMetadata($metadata);
        };

        $this->batchProcessor->processBatchForBatchItem(
            $this->batchObjectManager,
            $batch,
            $batchItem,
            $updateFreshBatch
        );
    }
}
