<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Decisions;

use EonX\EasyDecision\Decisions\ValueDecision;
use EonX\EasyDecision\Exceptions\ContextNotSetException;
use EonX\EasyDecision\Exceptions\MissingValueIndexException;
use EonX\EasyDecision\Exceptions\ReservedContextIndexException;
use EonX\EasyDecision\Exceptions\UnableToMakeDecisionException;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;
use EonX\EasyDecision\Tests\Stubs\RuleWithNonBlockingErrorStub;

final class ValueDecisionTest extends AbstractTestCase
{
    /**
     * Data provider for testDecisionEntirely.
     *
     * @return iterable<mixed>
     */
    public function decisionEntirelyProvider(): iterable
    {
        yield 'No rules, no default output' => [
            [],
            ['value' => 5],
            5,
            []
        ];

        yield 'No rules, explicit default output' => [
            [],
            ['value' => 5],
            10,
            [],
            null,
            10
        ];
    }

    /**
     * Decision behave as expected.
     *
     * @param mixed[] $rules
     * @param mixed[] $input
     * @param mixed $expectedOutput
     * @param mixed[] $expectedRulesOutput
     * @param null|string $name
     * @param null|mixed $defaultOutput
     *
     * @return void
     *
     * @dataProvider decisionEntirelyProvider
     */
    public function testDecisionEntirely(
        array $rules,
        array $input,
        $expectedOutput,
        array $expectedRulesOutput,
        ?string $name = null,
        $defaultOutput = null
    ): void {
        $decision = (new ValueDecision($name))
            ->addRules($rules)
            ->setDefaultOutput($defaultOutput);

        $output = $decision->make($input);
        $context = $decision->getContext();

        self::assertEquals($expectedOutput, $output);
        self::assertEquals($name ?? '<no-name>', $decision->getName());
        self::assertEquals(ValueDecision::class, $context->getDecisionType());
        self::assertEquals($input, $context->getOriginalInput());
        self::assertEquals($expectedRulesOutput, $context->getRuleOutputs());
    }

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
     * @return \EonX\EasyDecision\Interfaces\RuleInterface
     */
    private function getExceptionRule(): RuleInterface
    {
        return new class implements RuleInterface {
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
     * @return \EonX\EasyDecision\Interfaces\RuleInterface
     */
    private function getModifyValueRuleInArray(): RuleInterface
    {
        return new class implements RuleInterface {
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
