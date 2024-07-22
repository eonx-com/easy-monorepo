<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Messenger\Message;

final class UpdateBatchItemMessage
{
    private const ONLY = [
        'attempts',
        'finished_at',
        'started_at',
        'status',
    ];

    private array $data = [];

    public function __construct(
        private readonly int|string $batchItemId,
        array $data,
        private readonly ?array $errorDetails = null,
    ) {
        foreach ($data as $name => $value) {
            if (\in_array($name, self::ONLY, true)) {
                $this->data[$name] = $value;
            }
        }
    }

    public function getBatchItemId(): int|string
    {
        return $this->batchItemId;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }
}
