<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Stubs;

use PHPUnit\Framework\TestCase;
use StepTheFkUp\EasyDecision\Interfaces\ContextAwareInterface;
use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;
use StepTheFkUp\EasyDecision\Interfaces\RuleInterface;

final class AssertContextAwareInputRule implements RuleInterface
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
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return mixed
     */
    public function proceed(ContextInterface $context)
    {
        $input = $context->getInput();

        TestCase::assertInstanceOf(ContextAwareInterface::class, $input);
        TestCase::assertEquals(\spl_object_hash($context), \spl_object_hash($input->getContext()));

        return $input;
    }

    /**
     * Check if rule supports given input.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return bool
     */
    public function supports(ContextInterface $context): bool
    {
        return $context->getInput() instanceof ContextAwareInterface;
    }

    /**
     * Get string representation of the rule.
     *
     * @return string
     */
    public function toString(): string
    {
        return 'test_context_aware_rule';
    }
}
