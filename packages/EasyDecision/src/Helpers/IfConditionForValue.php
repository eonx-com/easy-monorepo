<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Helpers;

final class IfConditionForValue
{
    public function __construct(
        private bool $condition,
        private mixed $value,
    ) {
    }

    public function else(mixed $value): self
    {
        if ($this->condition === false) {
            $this->value = $value;
        }

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function then(mixed $value): self
    {
        if ($this->condition === true) {
            $this->value = $value;
        }

        return $this;
    }
}
