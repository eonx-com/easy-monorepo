<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Tests;

use EonX\EasyDecision\Exceptions\InvalidArgumentException;
use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Expressions\ExpressionFunctionFactory;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionFunctionFactoryInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionFunctionFactoryTest extends AbstractTestCase
{
    public function testCreateFromAssociativeArraySuccessfully(): void
    {
        $input = [
            'name' => 'cap',
            'evaluator' => function ($arguments, $value, $max) {
                return \min($value, $max);
            },
        ];

        $function = $this->getFactory()->create($input);

        self::assertEquals($input['name'], $function->getName());
        self::assertEquals($input['evaluator'], $function->getEvaluator());
    }

    public function testCreateFromBaseExpressionFunction(): void
    {
        $baseExpressionFunction = BaseExpressionFunction::fromPhp('max');

        self::assertEquals('max', $this->getFactory()->create($baseExpressionFunction)->getName());
    }

    public function testCreateFromSimpleArraySuccessfully(): void
    {
        $input = [
            'cap', function ($arguments, $value, $max) {
                        return \min($value, $max);
                    }];

        $function = $this->getFactory()->create($input);

        self::assertEquals($input[0], $function->getName());
        self::assertEquals($input[1], $function->getEvaluator());
    }

    public function testInstanceOfExpressionFunctionReturnItAsItIs(): void
    {
        $expressionFunction = new ExpressionFunction('function', function (): void {
        });

        self::assertEquals(
            \spl_object_hash($expressionFunction),
            \spl_object_hash($this->getFactory()->create($expressionFunction))
        );
    }

    public function testInvalidArrayInputException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getFactory()->create([]);
    }

    public function testNonArrayInputException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getFactory()->create('non-array');
    }

    private function getFactory(): ExpressionFunctionFactoryInterface
    {
        return new ExpressionFunctionFactory();
    }
}
