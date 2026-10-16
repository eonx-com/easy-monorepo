<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Stub\Message;

use EonX\EasyLock\Common\ValueObject\LockData;
use EonX\EasyLock\Common\ValueObject\WithLockDataInterface;

final readonly class WithLockDataMessageStub implements WithLockDataInterface
{
    public function __construct(
        private LockData $lockData,
    ) {
    }

    public function getLockData(): LockData
    {
        return $this->lockData;
    }
}
