<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Messenger\Message;

final readonly class ProcessBatchForBatchItemMessage
{
    public function __construct(
        private int|string $batchItemId,
        private ?array $errorDetails = null,
    ) {
    }

    public function getBatchItemId(): int|string
    {
        return $this->batchItemId;
    }

    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }
}
