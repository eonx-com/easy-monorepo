<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Decisions;

use StepTheFkUp\EasyDecision\Decisions\ValueDecision;
use StepTheFkUp\EasyDecision\Exceptions\ContextNotSetException;
use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;
use StepTheFkUp\EasyDecision\Interfaces\RuleInterface;
use StepTheFkUp\EasyDecision\Tests\AbstractTestCase;

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

        (new ValueDecision([$this->createUnsupportedRule('whatever')]))->getContext();
    }

    /**
     * Decision should throw an exception when invalid rule given.
     *
     * @return void
     */
    public function testNonRuleInterfaceException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ValueDecision(['not-a-rule']);
    }

    /**
     * Decision should return modified array input.
     *
     * @return void
     */
    public function testReturnModifiedArrayInputSuccessfully(): void
    {
        $modifyRule = $this->getModifyValueRuleInArray();

        $decision = new ValueDecision([
            $this->createUnsupportedRule('unsupported-1'),
            $modifyRule
        ]);

        $original = ['value' => 0];
        $expected = ['value' => 10];

        $expectedRuleOutput = [
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED,
            $modifyRule->toString() => $expected
        ];

        self::assertEquals($expected, $decision->make($original));
        self::assertEquals($expectedRuleOutput, $decision->getContext()->getRuleOutputs());
        self::assertEquals($original, $decision->getContext()->getOriginalInput());
    }

    /**
     * Decision should return modified object input.
     *
     * @return void
     */
    public function testReturnModifiedObjectInputSuccessfully(): void
    {
        $modifyRule = $this->getModifyValueRuleInObject();

        $decision = new ValueDecision([
            $this->createUnsupportedRule('unsupported-1'),
            $modifyRule
        ]);

        $original = new \stdClass();
        $original->value = 0;

        $expected = new \stdClass();
        $expected->value = 10;

        $expectedRuleOutput = [
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED,
            $modifyRule->toString() => $expected
        ];

        self::assertEquals($expected, $decision->make($original));
        self::assertEquals($expectedRuleOutput, $decision->getContext()->getRuleOutputs());
        self::assertEquals($original, $decision->getContext()->getOriginalInput());
    }

    /**
     * Get rule to modify value from input.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
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
             * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
             *
             * @return mixed
             */
            public function proceed(ContextInterface $context)
            {
                $input = $context->getInput();

                $input['value'] = $input['value'] + 10;

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
                return isset($context->getInput()['value']);
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

    /**
     * Get rule to modify value from input.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleInterface
     */
    private function getModifyValueRuleInObject(): RuleInterface
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
             * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
             *
             * @return mixed
             */
            public function proceed(ContextInterface $context)
            {
                $input = $context->getInput();

                $input->value = $input->value + 10;

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
                return \is_object($context->getInput()) && \property_exists($context->getInput(), 'value');
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
