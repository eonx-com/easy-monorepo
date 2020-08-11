<?php

declare(strict_types=1);

namespace EonX\EasyCore\Lock;

use Closure;

/**
 * @deprecated Since 2.4.31. Will be remove in 3.0. Use eonx-com/easy-lock package instead.
 */
trait ProcessWithLockTrait
{
    /**
     * @var \EonX\EasyCore\Lock\LockServiceInterface
     */
    private $lockService;

    /**
     * @required
     */
    public function setLockService(LockServiceInterface $lockService): void
    {
        $this->lockService = $lockService;
    }

    /**
     * @return null|mixed
     */
    protected function processWithLock(WithLockDataInterface $withLockData, Closure $func)
    {
        @\trigger_error(\sprintf(
            '%s is deprecated since 2.4.31 and will be removed in 3.0, Use eonx-com/easy-lock package instead.',
            static::class,
        ), \E_USER_DEPRECATED);

        $data = $withLockData->getLockData();
        $lock = $this->lockService->createLock($data->getResource(), $data->getTtl());

        if ($lock->acquire() === false) {
            return null;
        }

        try {
            return $func();
        } finally {
            $lock->release();
        }
    }
}
