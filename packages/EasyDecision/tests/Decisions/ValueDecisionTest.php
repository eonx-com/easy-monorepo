<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Decisions;

use LoyaltyCorp\EasyDecision\Decisions\ValueDecision;
use LoyaltyCorp\EasyDecision\Exceptions\ContextNotSetException;
use LoyaltyCorp\EasyDecision\Exceptions\MissingValueIndexException;
use LoyaltyCorp\EasyDecision\Exceptions\ReservedContextIndexException;
use LoyaltyCorp\EasyDecision\Exceptions\UnableToMakeDecisionException;
use LoyaltyCorp\EasyDecision\Interfaces\RuleInterface;
use LoyaltyCorp\EasyDecision\Tests\AbstractTestCase;
use LoyaltyCorp\EasyDecision\Tests\Stubs\RuleWithNonBlockingErrorStub;

final class ValueDecisionTest extends AbstractTestCase
{
    /**
     * Decision should throw an exception when trying to get context before calling make.
     *
     * @return void
     */
    public function testGetContextBeforeMakeException(): void
    {
        $this->expectException(ContextNotSetException::class);

        ((new ValueDecision())->addRules([$this->createUnsupportedRule('whatever')]))->getContext();
    }

    /**
     * Decision should handle gracefully the non blocking error and add the output to the context.
     *
     * @return void
     */
    public function testNonBlockingRuleErrorException(): void
    {
        $decision = (new ValueDecision())->addRule(new RuleWithNonBlockingErrorStub());

        $output = $decision->make(['value' => 10]);

        self::assertSame(['non-blocking-error' => 'non-blocking-error'], $decision->getContext()->getRuleOutputs());
        self::assertEquals(10, $output);
    }

    /**
     * Decision should throw an exception when given input is an array and "value" index isn't set.
     *
     * @return void
     */
    public function testNotSetValueInInputArrayException(): void
    {
        $this->expectException(MissingValueIndexException::class);

        $decision = (new ValueDecision())->addRules([$this->getModifyValueRuleInArray()]);

        $decision->make([]);
    }

    /**
     * Decision should throw an exception when given input is an array and contains the reserved "context" index.
     *
     * @return void
     */
    public function testReservedContextInInputArrayException(): void
    {
        $this->expectException(ReservedContextIndexException::class);

        $decision = (new ValueDecision())->addRules([$this->getModifyValueRuleInArray()]);

        $decision->make(['context' => 'I know it is bad...', 'value' => 'value']);
    }

    /**
     * Decision should return modified array input.
     *
     * @return void
     */
    public function testReturnModifiedArrayInputSuccessfully(): void
    {
        $modifyRule = $this->getModifyValueRuleInArray();

        $decision = (new ValueDecision())->addRules([
            $this->createUnsupportedRule('unsupported-1'),
            $modifyRule
        ]);

        $original = ['value' => 0];
        $expected = 10;

        $expectedRuleOutput = [
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED,
            $modifyRule->toString() => 10
        ];

        self::assertEquals($expected, $decision->make($original));
        self::assertEquals($expectedRuleOutput, $decision->getContext()->getRuleOutputs());
        self::assertEquals($original, $decision->getContext()->getOriginalInput());
    }

    /**
     * Decision should throw exception if anything goes wrong in rules.
     *
     * @return void
     */
    public function testUnableToMakeDecisionWhenExceptionInRules(): void
    {
        $this->expectException(UnableToMakeDecisionException::class);

        $decision = (new ValueDecision())->addRule($this->getExceptionRule());

        $decision->make(['value' => 1]);
    }

    /**
     * Get rule to throw exception.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface
     */
    private function getExceptionRule(): RuleInterface
    {
        return new class implements RuleInterface
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
                throw new \Exception('');
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
                return 'exception';
            }
        };
    }

    /**
     * Get rule to modify value from input.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface
     */
    private function getModifyValueRuleInArray(): RuleInterface
    {
        return new class implements RuleInterface
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
                return $input['value'] + 10;
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
                return isset($input['value']);
            }

            /**
             * Get string representation of the rule.
             *
             * @return string
             */
            public function toString(): string
            {
                return 'Add_10_to_value';
            }
        };
    }
}

\class_alias(
    ValueDecisionTest::class,
    'StepTheFkUp\EasyDecision\Tests\Decisions\ValueDecisionTest',
    false
);
