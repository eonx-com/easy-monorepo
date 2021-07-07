<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Stubs;

use Closure;
use EonX\EasyLock\Interfaces\LockDataInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use Symfony\Component\Lock\LockInterface;

final class LockServiceStub implements LockServiceInterface
{
    /**
     * @var bool
     */
    private $canProcess;

    /**
     * @var \EonX\EasyLock\Interfaces\LockDataInterface
     */
    private $lockData;

    public function __construct(?bool $canProcess = null)
    {
        $this->canProcess = $canProcess ?? true;
    }

    public function createLock(string $resource, ?float $ttl = null): LockInterface
    {
        throw new \RuntimeException('not required');
    }

    public function getLockData(): ?LockDataInterface
    {
        return $this->lockData;
    }

    /**
     * @return null|mixed
     */
    public function processWithLock(LockDataInterface $lockData, Closure $func)
    {
        $this->lockData = $lockData;

        return $this->canProcess ? $func() : null;
    }
}
