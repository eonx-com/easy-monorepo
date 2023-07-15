<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Decisions;

use EonX\EasyDecision\Decisions\ConsensusDecision;
use EonX\EasyDecision\Decisions\ValueDecision;
use EonX\EasyDecision\Helpers\ValueExpressionFunctionProvider;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;
use EonX\EasyDecision\Tests\Stubs\RuleStopPropagationStub;
use EonX\EasyDecision\Tests\Stubs\RuleStub;
use EonX\EasyDecision\Tests\Stubs\RuleWithExtraOutputStub;

final class DecisionsTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testDecisions
     */
    public static function providerTestDecisions(): iterable
    {
        yield 'No rules' => [
            new ValueDecision(),
            [],
            [
                'value' => 5,
            ],
            5,
            [],
        ];

        yield 'Simple rule' => [
            new ValueDecision(),
            [self::createLanguageRule('add(5)')],
            [
                'value' => 5,
            ],
            10,
            [
                'add(5)' => 10,
            ],
        ];

        yield 'Rules with name and extra' => [
            new ValueDecision(),
            [
                self::createLanguageRule('add(5)'),
                self::createLanguageRule('add(10)', null, 'Add 10'),
                self::createLanguageRule('add(20)', null, null, [
                    'key' => 'value',
                ]),
                self::createLanguageRule('add(30)', null, 'Add 30', [
                    'key1' => 'value1',
                ]),
            ],
            [
                'value' => 5,
            ],
            70,
            [
                'add(5)' => 10,
                'Add 10' => 20,
                'add(20)' => [
                    'output' => 40,
                    'key' => 'value',
                ],
                'Add 30' => [
                    'output' => 70,
                    'key1' => 'value1',
                ],
            ],
        ];

        yield 'Consensus with name and extra' => [
            new ConsensusDecision(),
            [
                new RuleWithExtraOutputStub('Unsupported with extra', false, [
                    'key' => 'value',
                ], false),
                new RuleStub('Only false', false),
                new RuleStub('Only true', true),
                new RuleWithExtraOutputStub('True with extra', true, [
                    'key' => 'value',
                ]),
            ],
            [],
            true,
            [
                'Unsupported with extra' => [
                    'output' => RuleInterface::OUTPUT_UNSUPPORTED,
                    'key' => 'value',
                ],
                'Only false' => false,
                'Only true' => true,
                'True with extra' => [
                    'output' => true,
                    'key' => 'value',
                ],
            ],
        ];

        yield 'Exit on propagation stopped' => [
            (new ValueDecision())->setExitOnPropagationStopped(),
            [
                self::createLanguageRule('add(5)'),
                new RuleStopPropagationStub('exit-on-propagation-stopped', 10, true),
                self::createLanguageRule('add(10)'),
            ],
            [
                'value' => 5,
            ],
            10,
            [
                'add(5)' => 10,
                'exit-on-propagation-stopped' => 10,
            ],
        ];
    }

    /**
     * @param \EonX\EasyDecision\Interfaces\RuleInterface[] $rules
     * @param mixed[] $input
     * @param mixed[] $expectedRulesOutput
     * @dataProvider providerTestDecisions
     */
    public function testDecisions(
        DecisionInterface $decision,
        array $rules,
        array $input,
        mixed $expectedOutput,
        array $expectedRulesOutput,
    ): void {
        $expressionLanguage = $this->createExpressionLanguage();
        $expressionLanguage->addFunctions((new ValueExpressionFunctionProvider())->getFunctions());

        $decision->setExpressionLanguage($expressionLanguage);

        $output = $decision
            ->addRules($rules)
            ->make($input);
        $context = $decision->getContext();

        self::assertEquals($expectedOutput, $output);
        self::assertEquals($decision::class, $context->getDecisionType());
        self::assertEquals($input, $context->getOriginalInput());
        self::assertEquals($expectedRulesOutput, $context->getRuleOutputs());
    }
}
