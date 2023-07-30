<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Runtime;

final class SwooleContext
{
    private array $context = [];

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->context[$name] ?? $default;
    }

    public function has(string $name): bool
    {
        return isset($this->context[$name]);
    }

    public function set(string $name, mixed $value): void
    {
        $this->context[$name] = $value;
    }
}
