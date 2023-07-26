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
use Exception;

final class ValueDecisionTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testDecisionEntirely
     */
    public static function decisionEntirelyProvider(): iterable
    {
        yield 'No rules, no default output' => [
            [],
            [
                'value' => 5,
            ],
            5,
            [],
        ];

        yield 'No rules, explicit default output' => [
            [],
            [
                'value' => 5,
            ],
            10,
            [],
            null,
            10,
        ];
    }

    /**
     * @param mixed[] $rules
     * @param mixed[] $input
     * @param mixed[] $expectedRulesOutput
     * @param null|mixed $defaultOutput
     * @dataProvider decisionEntirelyProvider
     */
    public function testDecisionEntirely(
        array $rules,
        array $input,
        mixed $expectedOutput,
        array $expectedRulesOutput,
        ?string $name = null,
        $defaultOutput = null,
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

    public function testGetContextBeforeMakeException(): void
    {
        $this->expectException(ContextNotSetException::class);

        ((new ValueDecision())->addRules([$this->createUnsupportedRule('whatever')]))->getContext();
    }

    public function testNonBlockingRuleErrorException(): void
    {
        $decision = (new ValueDecision())->addRule(new RuleWithNonBlockingErrorStub());

        $output = $decision->make([
            'value' => 10,
        ]);

        self::assertEquals([
            'non-blocking-error' => 'non-blocking-error',
        ], $decision->getContext()
            ->getRuleOutputs());
        self::assertEquals(10, $output);
    }

    public function testNotSetValueInInputArrayException(): void
    {
        $this->expectException(MissingValueIndexException::class);

        $decision = (new ValueDecision())->addRules([$this->getModifyValueRuleInArray()]);

        $decision->make([]);
    }

    public function testReservedContextInInputArrayException(): void
    {
        $this->expectException(ReservedContextIndexException::class);

        $decision = (new ValueDecision())->addRules([$this->getModifyValueRuleInArray()]);

        $decision->make([
            'context' => 'I know it is bad...',
            'value' => 'value',
        ]);
    }

    public function testReturnModifiedArrayInputSuccessfully(): void
    {
        $modifyRule = $this->getModifyValueRuleInArray();

        $decision = (new ValueDecision())->addRules([$this->createUnsupportedRule('unsupported-1'), $modifyRule]);

        $original = [
            'value' => 0,
        ];
        $expected = 10;

        $expectedRuleOutput = [
            'unsupported-1' => RuleInterface::OUTPUT_UNSUPPORTED,
            $modifyRule->toString() => 10,
        ];

        self::assertEquals($expected, $decision->make($original));
        self::assertEquals($expectedRuleOutput, $decision->getContext()->getRuleOutputs());
        self::assertEquals($original, $decision->getContext()->getOriginalInput());
    }

    public function testUnableToMakeDecisionWhenExceptionInRules(): void
    {
        $this->expectException(UnableToMakeDecisionException::class);

        $decision = (new ValueDecision())->addRule($this->getExceptionRule());

        $decision->make([
            'value' => 1,
        ]);
    }

    private function getExceptionRule(): RuleInterface
    {
        return new class() implements RuleInterface {
            public function getPriority(): int
            {
                return 0;
            }

            /**
             * @param mixed[] $input
             */
            public function proceed(array $input): never
            {
                throw new Exception('');
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
                return 'exception';
            }
        };
    }

    private function getModifyValueRuleInArray(): RuleInterface
    {
        return new class() implements RuleInterface {
            public function getPriority(): int
            {
                return 0;
            }

            /**
             * @param mixed[] $input
             */
            public function proceed(array $input): mixed
            {
                return $input['value'] + 10;
            }

            /**
             * @param mixed[] $input
             */
            public function supports(array $input): bool
            {
                return isset($input['value']);
            }

            public function toString(): string
            {
                return 'Add_10_to_value';
            }
        };
    }
}
