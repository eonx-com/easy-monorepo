<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Decisions;

use EonX\EasyDecision\Decisions\ConsensusDecision;
use EonX\EasyDecision\Decisions\ValueDecision;
use EonX\EasyDecision\Exceptions\UnableToMakeDecisionException;
use EonX\EasyDecision\Expressions\ExpressionLanguageConfig;
use EonX\EasyDecision\Helpers\ValueExpressionFunctionProvider;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;
use EonX\EasyDecision\Tests\Stubs\RuleStub;

final class DecisionsTest extends AbstractTestCase
{
    /**
     * Data provider for testDecisions.
     *
     * @return iterable<mixed>
     */
    public function providerTestDecisions(): iterable
    {
        yield 'No rules' => [
            new ValueDecision(),
            [],
            ['value' => 5],
            5,
            []
        ];

        yield 'Simple rule' => [
            new ValueDecision(),
            [$this->createLanguageRule('add(5)')],
            ['value' => 5],
            10,
            ['add(5)' => 10]
        ];

        yield 'Rules with name and extra' => [
            new ValueDecision(),
            [
                $this->createLanguageRule('add(5)'),
                $this->createLanguageRule('add(10)', null, 'Add 10'),
                $this->createLanguageRule('add(20)', null, null, ['key' => 'value']),
                $this->createLanguageRule('add(30)', null, 'Add 30', ['key1' => 'value1'])
            ],
            ['value' => 5],
            70,
            [
                'add(5)' => 10,
                'Add 10' => 20,
                'add(20)' => ['output' => 40, 'key' => 'value'],
                'Add 30' => ['output' => 70, 'key1' => 'value1']
            ]
        ];

        yield 'Consensus with name and extra' => [
            new ConsensusDecision(),
            [
                new RuleStub('Only false', false),
                new RuleStub('Only true', true),
                new RuleStub('True in array', ['output' => true, 'key' => 'value'])
            ],
            [],
            true,
            [
                'Only false' => false,
                'Only true' => true,
                'True in array' => ['output' => true, 'key' => 'value']
            ]
        ];
    }

    /**
     * Decision should throw exception if rule return array output with no output key.
     *
     * @return void
     */
    public function testArrayOutputRequiresOutputKeyException(): void
    {
        $this->expectException(UnableToMakeDecisionException::class);

        (new ConsensusDecision())->addRules([new RuleStub('No Output', [])])->make([]);
    }

    /**
     * Test decisions.
     *
     * @param \EonX\EasyDecision\Interfaces\DecisionInterface $decision
     * @param \EonX\EasyDecision\Interfaces\RuleInterface[] $rules
     * @param mixed[] $input
     * @param mixed $expectedOutput
     * @param mixed[] $expectedRulesOutput
     *
     * @return void
     *
     * @dataProvider providerTestDecisions
     */
    public function testDecisions(
        DecisionInterface $decision,
        array $rules,
        array $input,
        $expectedOutput,
        array $expectedRulesOutput
    ): void {
        $this->injectExpressionLanguage(
            $rules,
            new ExpressionLanguageConfig(null, [new ValueExpressionFunctionProvider()])
        );

        $output = $decision->addRules($rules)->make($input);
        $context = $decision->getContext();

        self::assertEquals($expectedOutput, $output);
        self::assertEquals(\get_class($decision), $context->getDecisionType());
        self::assertEquals($input, $context->getOriginalInput());
        self::assertEquals($expectedRulesOutput, $context->getRuleOutputs());
    }
}
