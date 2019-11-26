<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Expressions;

use EonX\EasyDecision\Exceptions\InvalidArgumentException;
use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Expressions\ExpressionFunctionFactory;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use EonX\EasyDecision\Tests\AbstractTestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionFunctionFactoryTest extends AbstractTestCase
{
    /**
     * Factory should be able to create expression function from associative array.
     *
     * @return void
     */
    public function testCreateFromAssociativeArraySuccessfully(): void
    {
        $input = [
            'name' => 'cap',
            'evaluator' => function ($arguments, $value, $max) {
                return \min($value, $max);
            }
        ];

        $function = $this->getFactory()->create($input);

        self::assertEquals($input['name'], $function->getName());
        self::assertEquals($input['evaluator'], $function->getEvaluator());
    }

    /**
     * Factory should create function from base expression function.
     *
     * @return void
     */
    public function testCreateFromBaseExpressionFunction(): void
    {
        $baseExpressionFunction = BaseExpressionFunction::fromPhp('max');

        self::assertEquals('max', $this->getFactory()->create($baseExpressionFunction)->getName());
    }

    /**
     * Factory should be able to create expression function from simple array.
     *
     * @return void
     */
    public function testCreateFromSimpleArraySuccessfully(): void
    {
        $input = [
            'cap',
            function ($arguments, $value, $max) {
                return \min($value, $max);
            }
        ];

        $function = $this->getFactory()->create($input);

        self::assertEquals($input[0], $function->getName());
        self::assertEquals($input[1], $function->getEvaluator());
    }

    /**
     * Factory should return function as it is if already an ExpressionFunctionInterface.
     *
     * @return void
     */
    public function testInstanceOfExpressionFunctionReturnItAsItIs(): void
    {
        $expressionFunction = new ExpressionFunction('function', function (): void {
        });

        self::assertEquals(
            \spl_object_hash($expressionFunction),
            \spl_object_hash($this->getFactory()->create($expressionFunction))
        );
    }

    /**
     * Factory should throw exception if not able to create expression function from given array.
     *
     * @return void
     */
    public function testInvalidArrayInputException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getFactory()->create([]);
    }

    /**
     * Factory should throw exception if non-array input given.
     *
     * @return void
     */
    public function testNonArrayInputException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getFactory()->create('non-array');
    }

    /**
     * Get expression function factory.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
     */
    private function getFactory(): ExpressionFunctionFactoryInterface
    {
        return new ExpressionFunctionFactory();
    }
}


