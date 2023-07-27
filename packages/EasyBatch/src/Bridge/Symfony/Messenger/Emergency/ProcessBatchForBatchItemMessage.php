<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Emergency;

final class ProcessBatchForBatchItemMessage
{
    public function __construct(
        private readonly int|string $batchItemId,
        private readonly ?array $errorDetails = null,
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
