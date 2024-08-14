<?php
declare(strict_types=1);

namespace EonX\EasyLock\Common\ValueObject;

final class LockData
{
    private bool $retry;

    public function __construct(
        private string $resource,
        private ?float $ttl = null,
        ?bool $retry = null,
    ) {
        $this->retry = $retry ?? false;
    }

    public static function create(string $resource, ?float $ttl = null, ?bool $retry = null): self
    {
        return new self($resource, $ttl, $retry);
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getTtl(): ?float
    {
        return $this->ttl;
    }

    public function shouldRetry(): bool
    {
        return $this->retry;
    }

    public function update(?string $resource = null, ?float $ttl = null, ?bool $shouldRetry = null): self
    {
        $this->resource = $resource ?? $this->resource;
        $this->ttl = $ttl ?? $this->ttl;
        $this->retry = $shouldRetry ?? $this->retry;

        return $this;
    }
}
