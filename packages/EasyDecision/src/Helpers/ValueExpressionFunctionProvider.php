<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Helpers;

use LoyaltyCorp\EasyDecision\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyDecision\Exceptions\MissingValueIndexException;
use LoyaltyCorp\EasyDecision\Expressions\ExpressionFunction;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;

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
     * Add given value to given number or to value from input if number not provided.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function add(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('add', function ($arguments, $value, $number = null) {
            $this->validateArguments($arguments, $number);

            return ($number ?? $arguments['value']) + $value;
        });
    }

    /**
     * Divide by value the given number or value from input if number not provided.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function divide(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('divide', function ($arguments, $value, $number = null) {
            $this->validateArguments($arguments, $number);

            if ($value === 0) {
                throw new InvalidArgumentException('Cannot divide by 0');
            }

            return ($number ?? $arguments['value']) / $value;
        });
    }

    /**
     * Check if given target strictly equal given value.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function equal(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('equal', function ($arguments, $target, $value) {
             return $target === $value;
        });
    }

    /**
     * Create if condition.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function if(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('if', function ($arguments, $condition) {
            $this->validateArguments($arguments);

            return new IfConditionForValue((bool)$condition, $arguments['value']);
        });
    }

    /**
     * Multiply by value the given number or value from input if number not provided.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function multiply(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('multiply', function ($arguments, $value, $number = null) {
            $this->validateArguments($arguments, $number);

            return ($number ?? $arguments['value']) * $value;
        });
    }

    /**
     * Subtract given value to given number or to value from input if number not provided.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     */
    private function subtract(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('subtract', function ($arguments, $value, $number = null) {
            $this->validateArguments($arguments, $number);

            return ($number ?? $arguments['value']) - $value;
        });
    }

    /**
     * Validate given argument have "value" index.
     *
     * @param mixed[] $arguments
     * @param null|mixed $number
     *
     * @return void
     */
    private function validateArguments(array $arguments, $number = null): void
    {
        if (isset($arguments['value']) === false && $number === null) {
            throw new MissingValueIndexException('Missing "value" in input');
        }
    }
}
