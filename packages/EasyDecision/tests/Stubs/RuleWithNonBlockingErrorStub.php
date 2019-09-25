<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Stubs;

use LoyaltyCorp\EasyDecision\Interfaces\NonBlockingRuleErrorInterface;
use LoyaltyCorp\EasyDecision\Interfaces\RuleInterface;

final class RuleWithNonBlockingErrorStub implements RuleInterface
{
    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * Proceed with input.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function proceed(array $input)
    {
        throw new class extends \Exception implements NonBlockingRuleErrorInterface
        {
            /**
             * Get error output.
             *
             * @return string
             */
            public function getErrorOutput(): string
            {
                return 'non-blocking-error';
            }
        };
    }

    /**
     * Check if rule supports given input.
     *
     * @param mixed[] $input
     *
     * @return bool
     */
    public function supports(array $input): bool
    {
        return true;
    }

    /**
     * Get string representation of the rule.
     *
     * @return string
     */
    public function toString(): string
    {
        return 'non-blocking-error';
    }
}
