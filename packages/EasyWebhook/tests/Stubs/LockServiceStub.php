<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use Closure;
use EonX\EasyLock\Interfaces\LockDataInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use RuntimeException;
use Symfony\Component\Lock\LockInterface;

final class LockServiceStub implements LockServiceInterface
{
    private bool $canProcess;

    private ?LockDataInterface $lockData = null;

    public function __construct(?bool $canProcess = null)
    {
        $this->canProcess = $canProcess ?? true;
    }

    public function createLock(string $resource, ?float $ttl = null): LockInterface
    {
        throw new RuntimeException('Not required.');
    }

    public function getLockData(): ?LockDataInterface
    {
        return $this->lockData;
    }

    public function processWithLock(LockDataInterface $lockData, Closure $func): mixed
    {
        $this->lockData = $lockData;

        return $this->canProcess ? $func() : null;
    }
}
