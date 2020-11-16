<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Response\Data;

final class ErrorResponseFormat
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
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
