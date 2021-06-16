<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class BatchItemStamp implements StampInterface
{
    /**
     * @var int
     */
    private $attempts;

    /**
     * @var int|string
     */
    private $batchItemId;

    /**
     * @param int|string $batchItemId
     */
    public function __construct($batchItemId, int $attempts)
    {
        $this->batchItemId = $batchItemId;
        $this->attempts = $attempts;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @return int|string
     */
    public function getBatchItemId()
    {
        return $this->batchItemId;
    }
}
