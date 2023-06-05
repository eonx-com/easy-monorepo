<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Response\Data;

final class ErrorResponseFormat
{
    public function __construct(
        private readonly string $key,
        private readonly string $value,
    ) {
    }

    public static function create(string $key, string $value): self
    {
        return new self($key, $value);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
