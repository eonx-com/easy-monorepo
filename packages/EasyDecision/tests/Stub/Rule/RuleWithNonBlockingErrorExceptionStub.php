<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Rule;

use EonX\EasyDecision\Exception\NonBlockingRuleErrorExceptionInterface;
use EonX\EasyDecision\Rule\RuleInterface;
use Exception;

final class RuleWithNonBlockingErrorExceptionStub implements RuleInterface
{
    public function getPriority(): int
    {
        return 0;
    }

    public function proceed(array $input): never
    {
        throw new class() extends Exception implements NonBlockingRuleErrorExceptionInterface {
            /**
             * Get error output.
             */
            public function getErrorOutput(): string
            {
                return 'non-blocking-error';
            }
        };
    }

    public function supports(array $input): bool
    {
        return true;
    }

    public function toString(): string
    {
        return 'non-blocking-error';
    }
}
