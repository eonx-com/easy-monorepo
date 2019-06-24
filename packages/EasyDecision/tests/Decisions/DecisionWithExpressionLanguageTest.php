<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Decisions;

use LoyaltyCorp\EasyDecision\Decisions\ValueDecision;
use LoyaltyCorp\EasyDecision\Expressions\ExpressionFunction;
use LoyaltyCorp\EasyDecision\Expressions\ExpressionLanguageConfig;
use LoyaltyCorp\EasyDecision\Helpers\ValueExpressionFunctionProvider;
use LoyaltyCorp\EasyDecision\Interfaces\ExpressionLanguageAwareInterface;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
use LoyaltyCorp\EasyDecision\Tests\AbstractTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

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
        $expected = 11;

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
            $this->createLanguageRule('add(10)'),
            $this->createLanguageRule('if(name in ["Brad", "Matt"]).then(add(10))'),
            $this->createLanguageRule('if(equal(name, "Matt")).then(add(1000))'),
            $this->createLanguageRule('cap(value, 200)'),
            $this->createLanguageRule('if(extra_param1 > 10).then(add(extra_param1))'),
            $this->createLanguageRule('if(extra_param1 > 10).else(add(extra_param1))')
        ];

        $providers = [new ValueExpressionFunctionProvider()];
        $functions = [
            new ExpressionFunction('cap', function ($arguments, $value, $max) {
                return \min($value, $max);
            })
        ];

        $this->injectExpressionLanguage($rules, new ExpressionLanguageConfig(null, $providers, $functions));

        $decision = (new ValueDecision())->addRules($rules);

        $tests = [
            [
                'original' => ['value' => 0, 'name' => 'Nathan', 'extra_param1' => 1],
                'expected' => 11,
                'outputs' => [
                    'add(10)' => 10,
                    'if(name in ["Brad", "Matt"]).then(add(10))' => 10,
                    'if(equal(name, "Matt")).then(add(1000))' => 10,
                    'cap(value, 200)' => 10,
                    'if(extra_param1 > 10).then(add(extra_param1))' => 10,
                    'if(extra_param1 > 10).else(add(extra_param1))' => 11
                ]
            ],
            [
                'original' => ['value' => 0, 'name' => 'Brad', 'extra_param1' => 1],
                'expected' => 21,
                'outputs' => [
                    'add(10)' => 10,
                    'if(name in ["Brad", "Matt"]).then(add(10))' => 20,
                    'if(equal(name, "Matt")).then(add(1000))' => 20,
                    'cap(value, 200)' => 20,
                    'if(extra_param1 > 10).then(add(extra_param1))' => 20,
                    'if(extra_param1 > 10).else(add(extra_param1))' => 21
                ]
            ],
            [
                'original' => ['value' => 0, 'name' => 'Matt', 'extra_param1' => 1],
                'expected' => 201,
                'outputs' => [
                    'add(10)' => 10,
                    'if(name in ["Brad", "Matt"]).then(add(10))' => 20,
                    'if(equal(name, "Matt")).then(add(1000))' => 1020,
                    'cap(value, 200)' => 200,
                    'if(extra_param1 > 10).then(add(extra_param1))' => 200,
                    'if(extra_param1 > 10).else(add(extra_param1))' => 201
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
