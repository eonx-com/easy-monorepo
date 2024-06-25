<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger\Lock;

use EonX\EasyLock\Common\ValueObject\LockDataInterface;
use Symfony\Component\Messenger\Envelope;

interface BatchItemLockFactoryInterface
{
    public function createFromEnvelope(Envelope $envelope): LockDataInterface;
}
