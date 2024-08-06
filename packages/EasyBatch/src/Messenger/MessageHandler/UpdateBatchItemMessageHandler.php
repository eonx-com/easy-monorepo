<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Messenger\MessageHandler;

use Carbon\Carbon;
use DateTimeInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
use EonX\EasyBatch\Messenger\Message\ProcessBatchForBatchItemMessage;
use EonX\EasyBatch\Messenger\Message\UpdateBatchItemMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateBatchItemMessageHandler
{
    public function __construct(
        private BatchItemRepositoryInterface $batchItemRepository,
        private ProcessBatchForBatchItemMessageHandler $processBatchForBatchItemHandler,
        private string $dateTimeFormat,
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function __invoke(UpdateBatchItemMessage $message): void
    {
        $this->updateBatchItem(
            $this->batchItemRepository->findOrFail($message->getBatchItemId()),
            $message->getData(),
            $message->getErrorDetails()
        );

        // Trigger process batch handler directly from here
        $processBatchForBatchItemHandler = $this->processBatchForBatchItemHandler;
        $processBatchForBatchItemHandler(new ProcessBatchForBatchItemMessage($message->getBatchItemId()));
    }

    private function createDateTimeFromFormat(string $dateTime): DateTimeInterface
    {
        /** @var \DateTimeInterface $newDateTime */
        $newDateTime = Carbon::createFromFormat($this->dateTimeFormat, $dateTime, 'UTC');

        return $newDateTime;
    }

    private function updateBatchItem(BatchItemInterface $batchItem, array $data, ?array $errorDetails = null): void
    {
        $batchItem
            ->setAttempts($data['attempts'])
            ->setFinishedAt($this->createDateTimeFromFormat($data['finished_at']))
            ->setStartedAt($this->createDateTimeFromFormat($data['started_at']))
            ->setStatus($data['status']);

        $metadata = $batchItem->getMetadata() ?? [];
        $internal = $metadata['_internal'] ?? [];
        $now = Carbon::now('UTC')->format($this->dateTimeFormat);

        if (isset($internal['update_batch_item_emergency']) === false) {
            $internal['update_batch_item_emergency'] = [];
        }

        $internal['update_batch_item_emergency'][] = [
            'error_details' => $errorDetails,
            'triggered_at' => $now,
        ];

        $metadata['_internal'] = $internal;

        $batchItem->setMetadata($metadata);

        $this->batchItemRepository->save($batchItem);
    }
}
