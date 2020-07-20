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
     * @var null|float
     */
    private $ttl;

    public function __construct(string $resource, ?float $ttl = null)
    {
        $this->resource = $resource;
        $this->ttl = $ttl;
    }

    public static function create(string $resource, ?float $ttl = null): LockDataInterface
    {
        return new static($resource, $ttl);
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getTtl(): ?float
    {
        return $this->ttl;
    }
}
