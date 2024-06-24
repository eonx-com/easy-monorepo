<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps;

use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class BatchItemStamp implements StampInterface
{
    public function __construct(
        private int|string $batchItemId,
    ) {
    }

    public function getBatchItemId(): int|string
    {
        return $this->batchItemId;
    }
}
