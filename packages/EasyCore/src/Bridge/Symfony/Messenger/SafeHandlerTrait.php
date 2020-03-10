<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Messenger;

use Closure;
use EonX\EasyCore\Lock\LockServiceInterface;

trait SafeHandlerTrait
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
     * @return void|mixed
     */
    protected function handleSafely(string $resource, Closure $func, ?float $ttl = null)
    {
        $lock = $this->lockService->createLock($resource, $ttl);

        if ($lock->acquire() === false) {
            return;
        }

        try {
            return $func();
        } finally {
            $lock->release();
        }
    }
}
