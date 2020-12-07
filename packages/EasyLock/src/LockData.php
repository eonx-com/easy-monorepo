<?php

declare(strict_types=1);

namespace EonX\EasyLock;

use EonX\EasyLock\Interfaces\LockDataInterface;

final class LockData implements LockDataInterface
{
    /**
     * @var string
     */
    private $resource;

    /**
     * @var bool
     */
    private $retry;

    /**
     * @var null|float
     */
    private $ttl;

    public function __construct(string $resource, ?float $ttl = null, ?bool $retry = null)
    {
        $this->resource = $resource;
        $this->ttl = $ttl;
        $this->retry = $retry ?? false;
    }

    public static function create(string $resource, ?float $ttl = null, ?bool $retry = null): LockDataInterface
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
}
