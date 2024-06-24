<?php
declare(strict_types=1);

namespace EonX\EasyLock\Common\Locker;

use Closure;
use EonX\EasyLock\Common\ValueObject\WithLockDataInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ProcessWithLockTrait
{
    private LockerInterface $locker;

    #[Required]
    public function setLocker(LockerInterface $locker): void
    {
        $this->locker = $locker;
    }

    protected function processWithLock(WithLockDataInterface $withLockData, Closure $func): mixed
    {
        return $this->locker->processWithLock($withLockData->getLockData(), $func);
    }
}
