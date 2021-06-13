<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class BatchStamp implements StampInterface
{
    /**
     * @var int|string
     */
    private $batchId;

    /**
     * @param int|string $batchId
     */
    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * @return int|string
     */
    public function getBatchId()
    {
        return $this->batchId;
    }
}
