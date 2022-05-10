<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Emergency;

final class UpdateBatchItemMessage
{
    /**
     * @var string[]
     */
    private const ONLY = [
        'attempts',
        'finished_at',
        'started_at',
        'status',
    ];

    /**
     * @var mixed[]
     */
    private array $data = [];

    /**
     * @param mixed[] $data
     */
    public function __construct(
        private readonly int|string $batchItemId,
        array $data
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

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }
}
