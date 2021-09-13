<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
final class BatchStamp implements StampInterface
{
    /**
     * @var string
     */
    private $batchId;

    public function __construct(string $batchId)
    {
        $this->batchId = $batchId;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }
}
