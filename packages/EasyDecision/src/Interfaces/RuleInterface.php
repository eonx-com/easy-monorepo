<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface RuleInterface
{
    public const OUTPUT_SKIPPED = 'skipped';
    public const OUTPUT_UNSUPPORTED = 'unsupported';

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Proceed with input.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return mixed
     */
    public function proceed(ContextInterface $context);

    /**
     * Check if rule supports given input.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return bool
     */
    public function supports(ContextInterface $context): bool;

    /**
     * Get string representation of the rule.
     *
     * @return string
     */
    public function toString(): string;
}

\class_alias(
    RuleInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\RuleInterface',
    false
);
