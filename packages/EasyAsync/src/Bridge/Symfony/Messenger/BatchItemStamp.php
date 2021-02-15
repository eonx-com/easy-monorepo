<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class BatchItemStamp implements StampInterface
{
    /**
     * @var string
     */
    private $batchItemId;

    public function __construct(string $batchItemId)
    {
        $this->batchItemId = $batchItemId;
    }

    public function getBatchItemId(): string
    {
        return $this->batchItemId;
    }
}
