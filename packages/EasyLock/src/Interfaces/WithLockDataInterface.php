<?php
declare(strict_types=1);

namespace EonX\EasyLock\Interfaces;

interface WithLockDataInterface
{
    public function getLockData(): LockDataInterface;
}
