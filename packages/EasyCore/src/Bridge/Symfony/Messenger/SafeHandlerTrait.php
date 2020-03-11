<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Messenger;

use Closure;
use EonX\EasyCore\Lock\LockServiceInterface;
use EonX\EasyCore\Lock\ProcessWithLockTrait;

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
        @\trigger_error(\sprintf(
            '%s::%s() is deprecated since 2.3.3 and will be removed in 3.0, use %s::%s() instead',
            SafeHandlerTrait::class,
            __METHOD__,
            ProcessWithLockTrait::class,
            'processWithLock'
        ), \E_USER_DEPRECATED);

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
