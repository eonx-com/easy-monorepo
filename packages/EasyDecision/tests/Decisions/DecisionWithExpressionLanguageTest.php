<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Decisions;

use LoyaltyCorp\EasyDecision\Decisions\ValueDecision;
use LoyaltyCorp\EasyDecision\Expressions\ExpressionFunction;
use LoyaltyCorp\EasyDecision\Expressions\ExpressionLanguageConfig;
use LoyaltyCorp\EasyDecision\Interfaces\ExpressionLanguageAwareInterface;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
use LoyaltyCorp\EasyDecision\Tests\AbstractTestCase;

final class DecisionWithExpressionLanguageTest extends AbstractTestCase
{
    /**
     * Decision should modify given input based on expression language rule.
     *
     * @return void
     */
    public function testModifyValueInArray(): void
    {
        $rules = [$this->createLanguageRule('value + 10')];

        $this->injectExpressionLanguage($rules);

        $decision = (new ValueDecision())->addRules($rules);

        $original = ['value' => 1];
        $expected = ['value' => 11];

        self::assertEquals($expected, $decision->make($original));
    }

    /**
     * Decision should be able to proceed multiple inputs in a row.
     *
     * @return void
     */
    public function testSameDecisionWithDifferentInputs(): void
    {
        $rules = [
            $this->createLanguageRule('value + 10'),
            $this->createLanguageRule('name in ["Brad", "Matt"] ? value + 10 : value'),
            $this->createLanguageRule('name === "Matt" ? value + 1000 : value'),
            $this->createLanguageRule('cap(value, 200)'),
            $this->createLanguageRule('extra_param1 > 10 ? value + extra_param1 : value')
        ];

        $this->injectExpressionLanguage($rules, new ExpressionLanguageConfig(null, null, [
            new ExpressionFunction('cap', function ($arguments, $value, $max) {
                return \min($value, $max);
            })
        ]));

        $decision = (new ValueDecision())->addRules($rules);

        $tests = [
            [
                'original' => ['value' => 0, 'name' => 'Nathan', 'extra_param1' => 1],
                'expected' => ['value' => 10, 'name' => 'Nathan', 'extra_param1' => 1],
                'outputs' => [
                    'value + 10' => 10,
                    'name in ["Brad", "Matt"] ? value + 10 : value' => 10,
                    'name === "Matt" ? value + 1000 : value' => 10,
                    'cap(value, 200)' => 10,
                    'extra_param1 > 10 ? value + extra_param1 : value' => 10
                ]
            ],
            [
                'original' => ['value' => 0, 'name' => 'Brad', 'extra_param1' => 1],
                'expected' => ['value' => 20, 'name' => 'Brad', 'extra_param1' => 1],
                'outputs' => [
                    'value + 10' => 10,
                    'name in ["Brad", "Matt"] ? value + 10 : value' => 20,
                    'name === "Matt" ? value + 1000 : value' => 20,
                    'cap(value, 200)' => 20,
                    'extra_param1 > 10 ? value + extra_param1 : value' => 20
                ]
            ],
            [
                'original' => ['value' => 0, 'name' => 'Matt', 'extra_param1' => 1],
                'expected' => ['value' => 200, 'name' => 'Matt', 'extra_param1' => 1],
                'outputs' => [
                    'value + 10' => 10,
                    'name in ["Brad", "Matt"] ? value + 10 : value' => 20,
                    'name === "Matt" ? value + 1000 : value' => 1020,
                    'cap(value, 200)' => 200,
                    'extra_param1 > 10 ? value + extra_param1 : value' => 200
                ]
            ]
        ];

        foreach ($tests as $test) {
            self::assertEquals($test['expected'], $decision->make($test['original']));
            self::assertEquals($test['outputs'], $decision->getContext()->getRuleOutputs());
        }
    }

    /**
     * Inject expression language in rules.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface[] $rules
     * @param null|\LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     *
     * @return void
     */
    private function injectExpressionLanguage(array $rules, ?ExpressionLanguageConfigInterface $config = null): void
    {
        $expressionLanguage = $this->createExpressionLanguage($config);

        foreach ($rules as $rule) {
            if ($rule instanceof ExpressionLanguageAwareInterface) {
                $rule->setExpressionLanguage($expressionLanguage);
            }
        }
    }
}

\class_alias(
    DecisionWithExpressionLanguageTest::class,
    'StepTheFkUp\EasyDecision\Tests\Decisions\DecisionWithExpressionLanguageTest',
    false
);
