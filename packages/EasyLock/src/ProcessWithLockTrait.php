<?php
declare(strict_types=1);

namespace EonX\EasyLock;

use Closure;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\Interfaces\WithLockDataInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ProcessWithLockTrait
{
    private LockServiceInterface $lockService;

    #[Required]
    public function setLockService(LockServiceInterface $lockService): void
    {
        $this->lockService = $lockService;
    }

    protected function processWithLock(WithLockDataInterface $withLockData, Closure $func): mixed
    {
        return $this->lockService->processWithLock($withLockData->getLockData(), $func);
    }
}
