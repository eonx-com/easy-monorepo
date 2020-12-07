<?php

declare(strict_types=1);

namespace EonX\EasyLock;

use Closure;
use EonX\EasyLock\Exceptions\ShouldRetryException;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\Interfaces\WithLockDataInterface;

trait ProcessWithLockTrait
{
    /**
     * @var \EonX\EasyLock\Interfaces\LockServiceInterface
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
        $data = $withLockData->getLockData();
        $lock = $this->lockService->createLock($data->getResource(), $data->getTtl());

        if ($lock->acquire() === false) {
            // Throw exception to indicate we want ot retry
            if ($data->shouldRetry()) {
                throw new ShouldRetryException(\sprintf('Should retry "%s"', $data->getResource()));
            }

            return null;
        }

        try {
            return $func();
        } finally {
            $lock->release();
        }
    }
}
