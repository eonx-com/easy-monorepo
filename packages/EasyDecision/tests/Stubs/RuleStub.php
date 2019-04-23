<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Stubs;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;
use LoyaltyCorp\EasyDecision\Interfaces\RuleInterface;

final class RuleStub implements RuleInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $output;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var bool
     */
    private $supports;

    /**
     * RuleStub constructor.
     *
     * @param string $name
     * @param mixed $output
     * @param null|bool $supports
     * @param null|int $priority
     */
    public function __construct(string $name, $output, ?bool $supports = null, ?int $priority = null)
    {
        $this->name = $name;
        $this->output = $output;
        $this->supports = $supports ?? true;
        $this->priority = $priority ?? 0;
    }

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Proceed with input.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return mixed
     */
    public function proceed(ContextInterface $context)
    {
        return $this->output;
    }

    /**
     * Check if rule supports given input.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return bool
     */
    public function supports(ContextInterface $context): bool
    {
        return $this->supports;
    }

    /**
     * Get string representation of the rule.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->name;
    }
}

\class_alias(
    RuleStub::class,
    'StepTheFkUp\EasyDecision\Tests\Stubs\RuleStub',
    false
);
