<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Messenger\MessageHandler;

use Carbon\Carbon;
use EonX\EasyBatch\Common\Manager\BatchObjectManagerInterface;
use EonX\EasyBatch\Common\Processor\BatchProcessor;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchInterface;
use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;
use EonX\EasyBatch\Messenger\Message\ProcessBatchForBatchItemMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ProcessBatchForBatchItemMessageHandler
{
    public function __construct(
        private BatchItemRepositoryInterface $batchItemRepository,
        private BatchObjectManagerInterface $batchObjectManager,
        private BatchProcessor $batchProcessor,
        private BatchRepositoryInterface $batchRepository,
    ) {
    }
    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function __invoke(ProcessBatchForBatchItemMessage $message): void
    {
        $batchItem = $this->batchItemRepository->findOrFail($message->getBatchItemId());
        $batch = $this->batchRepository->findOrFail($batchItem->getBatchId());

        // Prevent running logic on already completed batch
        if ($batch->isCompleted()) {
            return;
        }

        // Update batch metadata to reflect emergency flow triggered
        $updateFreshBatch = static function (BatchInterface $freshBatch) use ($message): void {
            $metadata = $freshBatch->getMetadata() ?? [];
            $internal = $metadata['_internal'] ?? [];
            $now = Carbon::now('UTC')->format(BatchObjectInterface::DATETIME_FORMAT);

            if (isset($internal['process_batch_emergency']) === false) {
                $internal['process_batch_emergency'] = [];
            }

            $internal['process_batch_emergency'][] = [
                'error_details' => $message->getErrorDetails(),
                'triggered_at' => $now,
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
