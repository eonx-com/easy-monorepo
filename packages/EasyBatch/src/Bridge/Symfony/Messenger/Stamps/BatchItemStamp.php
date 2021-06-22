<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class BatchItemStamp implements StampInterface
{
    /**
     * @var int|string
     */
    private $batchItemId;

    /**
     * @param int|string $batchItemId
     */
    public function __construct($batchItemId)
    {
        $this->batchItemId = $batchItemId;
    }

    /**
     * @return int|string
     */
    public function getBatchItemId()
    {
        return $this->batchItemId;
    }
}
