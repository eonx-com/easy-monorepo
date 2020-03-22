<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Stubs;

use Symfony\Component\Lock\LockInterface;

final class LockStub implements LockInterface
{
    /**
     * @var bool
     */
    private $acquire;

    public function __construct(bool $acquire)
    {
        $this->acquire = $acquire;
    }

    /**
     * @param bool $blocking
     */
    public function acquire($blocking = false): bool
    {
        return $this->acquire;
    }

    public function getRemainingLifetime(): ?float
    {
        return null;
    }

    public function isAcquired(): bool
    {
        return $this->acquire;
    }

    public function isExpired(): bool
    {
        return false;
    }

    public function refresh(): void
    {
    }

    public function release(): void
    {
    }
}
