<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Expressions;

use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionFunctionFactory implements ExpressionFunctionFactoryInterface
{
    /**
     * Create expression function for given value.
     *
     * @param mixed $expressionFunction
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    public function create($expressionFunction): ExpressionFunctionInterface
    {
        if ($expressionFunction instanceof ExpressionFunctionInterface) {
            return $expressionFunction;
        }

        if ($expressionFunction instanceof BaseExpressionFunction) {
            return new ExpressionFunction($expressionFunction->getName(), $expressionFunction->getEvaluator());
        }

        if (\is_array($expressionFunction) === false) {
            throw new InvalidArgumentException(\sprintf(
                '%s::create expects either %s or array, %s given',
                self::class,
                ExpressionFunctionInterface::class,
                \gettype($expressionFunction)
            ));
        }

        // ['name' => 'myFunction', 'evaluator' => function () {}]
        if (\is_string($expressionFunction['name'] ?? null) && \is_callable($expressionFunction['evaluator'] ?? null)) {
            return new ExpressionFunction($expressionFunction['name'], $expressionFunction['evaluator']);
        }

        // ['myFunction', function () {}]
        if (\is_string($expressionFunction[0] ?? null) && \is_callable($expressionFunction[1] ?? null)) {
            return new ExpressionFunction($expressionFunction[0], $expressionFunction[1]);
        }

        throw new InvalidArgumentException(\sprintf(
            '%s::create called with array expects either "[\'name\' => \'myFunction\', 
                    \'evaluator\' => function () {}]" or "[\'myFunction\', function () {}]"',
            self::class
        ));
    }
}
