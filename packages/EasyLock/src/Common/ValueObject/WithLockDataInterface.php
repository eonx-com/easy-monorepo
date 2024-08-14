<?php
declare(strict_types=1);

namespace EonX\EasyLock\Common\ValueObject;

interface WithLockDataInterface
{
    public function getLockData(): LockData;
}
