<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Helpers;

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
     * IfConditionForValue constructor.
     *
     * @param bool $condition
     * @param mixed $value
     */
    public function __construct(bool $condition, $value)
    {
        $this->condition = $condition;
        $this->value = $value;
    }

    /**
     * Set current value to given value if condition is false.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function else($value)
    {
        if ($this->condition === false) {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * Get value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set current value to given value if condition is true.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function then($value)
    {
        if ($this->condition === true) {
            $this->value = $value;
        }

        return $this;
    }
}
