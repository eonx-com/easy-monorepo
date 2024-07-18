<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ValueObject;

final readonly class ErrorResponseFormat
{
    public function __construct(
        private string $key,
        private string $value,
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
