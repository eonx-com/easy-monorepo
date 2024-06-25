<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Lock;

use EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp;
use EonX\EasyLock\Common\ValueObject\LockData;
use EonX\EasyLock\Common\ValueObject\LockDataInterface;
use Symfony\Component\Messenger\Envelope;

final readonly class BatchItemLockFactory implements BatchItemLockFactoryInterface
{
    public function __construct(
        private ?float $ttl = null,
    ) {
    }

    public function createFromEnvelope(Envelope $envelope): LockDataInterface
    {
        /** @var \EonX\EasyBatch\Bridge\Symfony\Messenger\Stamps\BatchItemStamp $batchItemStamp */
        $batchItemStamp = $envelope->last(BatchItemStamp::class);
        $batchItemId = $batchItemStamp->getBatchItemId();

        return LockData::create(\sprintf('easy_batch_item_%s', $batchItemId), $this->ttl, true);
    }
}
