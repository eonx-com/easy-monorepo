<?php
declare(strict_types=1);

namespace EonX\EasyLock\Interfaces;

interface LockDataInterface
{
    public function getResource(): string;

    public function getTtl(): ?float;

    public function shouldRetry(): bool;

    public function update(?string $resource = null, ?float $ttl = null, ?bool $shouldRetry = null): self;
}
