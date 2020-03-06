<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Expressions;

use EonX\EasyDecision\Exceptions\InvalidArgumentException;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionFunctionFactory implements ExpressionFunctionFactoryInterface
{
    /**
     * @param mixed $expressionFunction
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

        // ['name' => 'myFunction', 'evaluator' => function () {}, 'description' => 'Optional text']
        if (\is_string($expressionFunction['name'] ?? null) && \is_callable($expressionFunction['evaluator'] ?? null)) {
            return new ExpressionFunction(
                $expressionFunction['name'],
                $expressionFunction['evaluator'],
                $expressionFunction['description'] ?? null
            );
        }

        // ['myFunction', function () {}, 'Optional text']
        if (\is_string($expressionFunction[0] ?? null) && \is_callable($expressionFunction[1] ?? null)) {
            return new ExpressionFunction(
                $expressionFunction[0],
                $expressionFunction[1],
                $expressionFunction[2] ?? null
            );
        }

        throw new InvalidArgumentException(\sprintf(
            '%s::create called with array expects either "[\'name\' => \'myFunction\', 
                    \'evaluator\' => function () {}]" or "[\'myFunction\', function () {}]"',
            self::class
        ));
    }
}
