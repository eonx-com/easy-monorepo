<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Helpers;

final class IfConditionForValue
{
    /**
     * @var bool
     */
    private $condition;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(bool $condition, $value)
    {
        $this->condition = $condition;
        $this->value = $value;
    }

    /**
     * @param mixed $value
     */
    public function else($value): self
    {
        if ($this->condition === false) {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function then($value): self
    {
        if ($this->condition === true) {
            $this->value = $value;
        }

        return $this;
    }
}
