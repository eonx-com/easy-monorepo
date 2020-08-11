<?php

declare(strict_types=1);

namespace EonX\EasyCore\Lock;

/**
 * @deprecated Since 2.4.31. Will be remove in 3.0. Use eonx-com/easy-lock package instead.
 */
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
        @\trigger_error(\sprintf(
            '%s is deprecated since 2.4.31 and will be removed in 3.0, Use eonx-com/easy-lock package instead.',
            static::class,
        ), \E_USER_DEPRECATED);

        $this->resource = $resource;
        $this->ttl = $ttl;
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
