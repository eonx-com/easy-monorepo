<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface RuleInterface
{
    public const OUTPUT_SKIPPED = 'skipped';
    public const OUTPUT_UNSUPPORTED = 'unsupported';

    /**
     * Proceed with input.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return mixed
     */
    public function proceed(ContextInterface $context);

    /**
     * Check if rule supports given input.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
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
