<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Stubs;

use LoyaltyCorp\EasyDecision\Interfaces\ContextAwareInterface;
use LoyaltyCorp\EasyDecision\Interfaces\ValueInputInterface;
use LoyaltyCorp\EasyDecision\Traits\ContextAwareTrait;

final class ValueContextAwareInputStub implements ContextAwareInterface, ValueInputInterface
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
     * @param mixed $value
     *
     * @return void
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}

\class_alias(
    ValueContextAwareInputStub::class,
    'StepTheFkUp\EasyDecision\Tests\Stubs\ValueContextAwareInputStub',
    false
);
