<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Stubs;

use StepTheFkUp\EasyDecision\Interfaces\ContextAwareInterface;
use StepTheFkUp\EasyDecision\Traits\ContextAwareTrait;

final class ValueContextAwareInputStub implements ContextAwareInterface
{
    use ContextAwareTrait;

    /**
     * @var int
     */
    private $value;

    /**
     * ValueContextAwareInputStub constructor.
     *
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * Get value.
     *
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Set value.
     *
     * @param int $value
     *
     * @return \StepTheFkUp\EasyDecision\Tests\Stubs\ValueContextAwareInputStub
     */
    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }
}
