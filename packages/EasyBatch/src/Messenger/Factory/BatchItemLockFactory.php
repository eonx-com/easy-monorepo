<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Messenger\Factory;

use EonX\EasyBatch\Messenger\Stamp\BatchItemStamp;
use EonX\EasyLock\Common\ValueObject\LockData;
use EonX\EasyLock\Common\ValueObject\LockDataInterface;
use Symfony\Component\Messenger\Envelope;

final class BatchItemLockFactory implements BatchItemLockFactoryInterface
{
    public function __construct(
        private readonly ?float $ttl = null,
    ) {
    }

    public function createFromEnvelope(Envelope $envelope): LockDataInterface
    {
        /** @var \EonX\EasyBatch\Messenger\Stamp\BatchItemStamp $batchItemStamp */
        $batchItemStamp = $envelope->last(BatchItemStamp::class);
        $batchItemId = $batchItemStamp->getBatchItemId();

        return LockData::create(\sprintf('easy_batch_item_%s', $batchItemId), $this->ttl, true);
    }
}
