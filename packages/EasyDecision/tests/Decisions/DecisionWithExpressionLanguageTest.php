<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Decisions;

use StepTheFkUp\EasyDecision\Decisions\ValueDecision;
use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Expressions\ExpressionFunction;
use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguageConfig;
use StepTheFkUp\EasyDecision\Tests\AbstractTestCase;

final class DecisionWithExpressionLanguageTest extends AbstractTestCase
{
    /**
     * Decision should throw exception when passing an ExpressionLanguageAware rule but no ExpressionLanguage set.
     *
     * @return void
     */
    public function testExpressionLanguageRuleButNoExpressionLanguageException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ValueDecision([$this->createLanguageRule('value + 10')]);
    }

    /**
     * Decision should modify given input based on expression language rule.
     *
     * @return void
     */
    public function testModifyValueInArray(): void
    {
        $decision = new ValueDecision(
            [$this->createLanguageRule('value + 10')],
            null,
            $this->createExpressionLanguage()
        );

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
            $this->createLanguageRule('cap(value, 200)')
        ];

        $config = new ExpressionLanguageConfig(null, null, [
            new ExpressionFunction('cap', function ($arguments, $value, $max) {
                return \min($value, $max);
            })
        ]);

        $decision = new ValueDecision($rules, null, $this->createExpressionLanguage($config));

        $tests = [
            [
                'original' => ['value' => 0, 'name' => 'Nathan'],
                'expected' => ['value' => 10, 'name' => 'Nathan'],
                'outputs' => [
                    'value + 10' => 10,
                    'name in ["Brad", "Matt"] ? value + 10 : value' => 10,
                    'name === "Matt" ? value + 1000 : value' => 10,
                    'cap(value, 200)' => 10
                ]
            ],
            [
                'original' => ['value' => 0, 'name' => 'Brad'],
                'expected' => ['value' => 20, 'name' => 'Brad'],
                'outputs' => [
                    'value + 10' => 10,
                    'name in ["Brad", "Matt"] ? value + 10 : value' => 20,
                    'name === "Matt" ? value + 1000 : value' => 20,
                    'cap(value, 200)' => 20
                ]
            ],
            [
                'original' => ['value' => 0, 'name' => 'Matt'],
                'expected' => ['value' => 200, 'name' => 'Matt'],
                'outputs' => [
                    'value + 10' => 10,
                    'name in ["Brad", "Matt"] ? value + 10 : value' => 20,
                    'name === "Matt" ? value + 1000 : value' => 1020,
                    'cap(value, 200)' => 200
                ]
            ]
        ];

        foreach ($tests as $test) {
            self::assertEquals($test['expected'], $decision->make($test['original']));
            self::assertEquals($test['outputs'], $decision->getContext()->getRuleOutputs());
        }
    }
}
