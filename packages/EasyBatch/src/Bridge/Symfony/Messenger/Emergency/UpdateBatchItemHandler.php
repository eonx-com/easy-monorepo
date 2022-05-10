<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Emergency;

use Carbon\Carbon;
use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UpdateBatchItemHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly BatchItemRepositoryInterface $batchItemRepository,
        private readonly ProcessBatchForBatchItemHandler $processBatchForBatchItemHandler
    ) {
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function __invoke(UpdateBatchItemMessage $message): void
    {
        $this->updateBatchItem(
            $this->batchItemRepository->findOrFail($message->getBatchItemId()),
            $message->getData()
        );

        // Trigger process batch handler directly from here
        $processBatchForBatchItemHandler = $this->processBatchForBatchItemHandler;
        $processBatchForBatchItemHandler(new ProcessBatchForBatchItemMessage($message->getBatchItemId()));
    }

    private function createDateTimeFromFormat(string $dateTime): \DateTimeInterface
    {
        /** @var \DateTimeInterface $newDateTime */
        $newDateTime = Carbon::createFromFormat(BatchObjectInterface::DATETIME_FORMAT, $dateTime, 'UTC');

        return $newDateTime;
    }

    /**
     * @param mixed[] $data
     */
    private function updateBatchItem(BatchItemInterface $batchItem, array $data): void
    {
        $batchItem
            ->setAttempts($data['attempts'])
            ->setFinishedAt($this->createDateTimeFromFormat($data['finished_at']))
            ->setStartedAt($this->createDateTimeFromFormat($data['started_at']))
            ->setStatus($data['status']);

        $metadata = $batchItem->getMetadata() ?? [];
        $internal = $metadata['_internal'] ?? [];

        $now = Carbon::now('UTC')->format(BatchObjectInterface::DATETIME_FORMAT);
        $internal['update_emergency_triggered_at'] = $now;

        $metadata['_internal'] = $internal;

        $batchItem->setMetadata($metadata);

        $this->batchItemRepository->save($batchItem);
    }
}
