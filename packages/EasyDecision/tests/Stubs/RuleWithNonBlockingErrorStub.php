<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\NonBlockingRuleErrorInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;

final class RuleWithNonBlockingErrorStub implements RuleInterface
{
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @param mixed[] $input
     */
    public function proceed(array $input): mixed
    {
        throw new class() extends \Exception implements NonBlockingRuleErrorInterface {
            /**
             * Get error output.
             */
            public function getErrorOutput(): string
            {
                return 'non-blocking-error';
            }
        };
    }

    /**
     * @param mixed[] $input
     */
    public function supports(array $input): bool
    {
        return true;
    }

    public function toString(): string
    {
        return 'non-blocking-error';
    }
}
