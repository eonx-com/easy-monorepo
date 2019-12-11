<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Helpers;

use EonX\EasyDecision\Exceptions\InvalidArgumentException;
use EonX\EasyDecision\Exceptions\MissingValueIndexException;
use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;

final class ValueExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * Get list of functions.
     *
     * @return mixed[]
     */
    public function getFunctions(): array
    {
        return [
            $this->add(),
            $this->divide(),
            $this->equal(),
            $this->if(),
            $this->multiply(),
            $this->subtract()
        ];
    }

    /**
     * Add given value to value from input.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function add(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('add', function ($arguments, $value) {
            $this->validateArguments($arguments);

            return $arguments['value'] + $value;
        }, 'Add given argument to value from input');
    }

    /**
     * Divide by value the value from input.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function divide(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('divide', function ($arguments, $value) {
            $this->validateArguments($arguments);

            if ($value === 0) {
                throw new InvalidArgumentException('Cannot divide by 0');
            }

            return $arguments['value'] / $value;
        }, 'Divide value from input by the given argument, cannot divide by 0');
    }

    /**
     * Check if given target strictly equal given value.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function equal(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('equal', function ($arguments, $target, $value) {
            return $target === $value;
        }, 'Check if the 2 given arguments are equal');
    }

    /**
     * Create if condition.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function if(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('if', function ($arguments, $condition) {
            $this->validateArguments($arguments);

            return new IfConditionForValue((bool)$condition, $arguments['value']);
        }, 'Create if condition allowing usage of ".then()" and/or "else()"');
    }

    /**
     * Multiply by value the value from input.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function multiply(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('multiply', function ($arguments, $value) {
            $this->validateArguments($arguments);

            return $arguments['value'] * $value;
        }, 'Multiply value from input by the given argument');
    }

    /**
     * Subtract given value to value from input.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function subtract(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('subtract', function ($arguments, $value) {
            $this->validateArguments($arguments);

            return $arguments['value'] - $value;
        }, 'Subtract given argument to value from input');
    }

    /**
     * Validate given argument have "value" index.
     *
     * @param mixed[] $arguments
     *
     * @return void
     */
    private function validateArguments(array $arguments): void
    {
        if (isset($arguments['value']) === false) {
            throw new MissingValueIndexException('Missing "value" in input');
        }
    }
}
