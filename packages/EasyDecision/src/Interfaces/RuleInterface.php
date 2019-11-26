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
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function proceed(array $input);

    /**
     * Check if rule supports given input.
     *
     * @param mixed[] $input
     *
     * @return bool
     */
    public function supports(array $input): bool;

    /**
     * Get string representation of the rule.
     *
     * @return string
     */
    public function toString(): string;
}


