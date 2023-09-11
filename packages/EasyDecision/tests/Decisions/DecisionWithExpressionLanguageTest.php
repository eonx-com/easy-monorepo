<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Decisions;

use EonX\EasyDecision\Decisions\ValueDecision;
use EonX\EasyDecision\Exceptions\UnableToMakeDecisionException;
use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Helpers\ValueExpressionFunctionProvider;
use EonX\EasyDecision\Tests\AbstractTestCase;

final class DecisionWithExpressionLanguageTest extends AbstractTestCase
{
    public function testExceptionIfExpressionLanguageNotSet(): void
    {
        $this->expectException(UnableToMakeDecisionException::class);
        $this->expectExceptionMessage(
            'Decision "<no-name>" of type "EonX\EasyDecision\Decisions\ValueDecision": ' .
            'Expression language not set, to use it in your rules you must set it on the decision instance'
        );

        (new ValueDecision())->addRule($this->createLanguageRule('value + 10'))
            ->make([
                'value' => 1,
            ]);
    }

    public function testModifyValueInArray(): void
    {
        $rules = [$this->createLanguageRule('value + 10')];
        $decision = (new ValueDecision())->addRules($rules);

        $this->injectExpressionLanguage($decision);

        $original = [
            'value' => 1,
        ];
        $expected = 11;

        self::assertEquals($expected, $decision->make($original));
    }

    public function testSameDecisionWithDifferentInputs(): void
    {
        $rules = [
            $this->createLanguageRule('add(10)', null, 'Add Ten'),
            $this->createLanguageRule('if(name in ["Brad", "Matt"]).then(add(10))'),
            $this->createLanguageRule('if(equal(name, "Matt")).then(add(1000))'),
            $this->createLanguageRule('cap(value, 200)'),
            $this->createLanguageRule('if(extra_param1 > 10).then(add(extra_param1))'),
            $this->createLanguageRule('if(extra_param1 > 10).else(add(extra_param1))'),
        ];

        $decision = (new ValueDecision())->addRules($rules);
        $expressionLanguage = $this->createExpressionLanguage();

        $expressionLanguage->addFunction(new ExpressionFunction(
            'cap',
            fn ($arguments, $value, $max) => \min($value, $max)
        ));
        $expressionLanguage->addFunctions((new ValueExpressionFunctionProvider())->getFunctions());

        $decision->setExpressionLanguage($expressionLanguage);

        $tests = [
            [
                'original' => [
                    'value' => 0,
                    'name' => 'Nathan',
                    'extra_param1' => 1,
                ],
                'expected' => 11,
                'outputs' => [
                    'Add Ten' => 10,
                    'if(name in ["Brad", "Matt"]).then(add(10))' => 10,
                    'if(equal(name, "Matt")).then(add(1000))' => 10,
                    'cap(value, 200)' => 10,
                    'if(extra_param1 > 10).then(add(extra_param1))' => 10,
                    'if(extra_param1 > 10).else(add(extra_param1))' => 11,
                ],
            ],
            [
                'original' => [
                    'value' => 0,
                    'name' => 'Brad',
                    'extra_param1' => 1,
                ],
                'expected' => 21,
                'outputs' => [
                    'Add Ten' => 10,
                    'if(name in ["Brad", "Matt"]).then(add(10))' => 20,
                    'if(equal(name, "Matt")).then(add(1000))' => 20,
                    'cap(value, 200)' => 20,
                    'if(extra_param1 > 10).then(add(extra_param1))' => 20,
                    'if(extra_param1 > 10).else(add(extra_param1))' => 21,
                ],
            ],
            [
                'original' => [
                    'value' => 0,
                    'name' => 'Matt',
                    'extra_param1' => 1,
                ],
                'expected' => 201,
                'outputs' => [
                    'Add Ten' => 10,
                    'if(name in ["Brad", "Matt"]).then(add(10))' => 20,
                    'if(equal(name, "Matt")).then(add(1000))' => 1020,
                    'cap(value, 200)' => 200,
                    'if(extra_param1 > 10).then(add(extra_param1))' => 200,
                    'if(extra_param1 > 10).else(add(extra_param1))' => 201,
                ],
            ],
        ];

        foreach ($tests as $test) {
            self::assertEquals($test['expected'], $decision->make($test['original']));
            self::assertEquals($test['outputs'], $decision->getContext()->getRuleOutputs());
        }
    }
}
