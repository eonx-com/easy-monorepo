<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Helpers;

use EonX\EasyDecision\Exceptions\InvalidArgumentException;
use EonX\EasyDecision\Exceptions\MissingValueIndexException;
use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionFunctionInterface;

final class ValueExpressionFunctionProvider
{
    public function getFunctions(): array
    {
        return [$this->add(), $this->divide(), $this->equal(), $this->if(), $this->multiply(), $this->subtract()];
    }

    private function add(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('add', function ($arguments, $value): int|float {
            $this->validateArguments($arguments);

            return $arguments['value'] + $value;
        }, 'Add given argument to value from input');
    }

    private function divide(): ExpressionFunctionInterface
    {
        return new ExpressionFunction('divide', function ($arguments, $value): int|float {
            $this->validateArguments($arguments);

            if ($value === 0) {
                throw new InvalidArgumentException('Cannot divide by 0');
            }

            return $arguments['value'] / $value;
        }, 'Divide value from input by the given argument, cannot divide by 0');
    }

    private function equal(): ExpressionFunctionInterface
    {
        return new ExpressionFunction(
            'equal',
            static fn ($arguments, $target, $value): bool => $target === $value,
            'Check if the 2 given arguments are equal'
        );
    }

    private function if(): ExpressionFunctionInterface
    {
        return new ExpressionFunction(
            'if',
            function ($arguments, $condition): IfConditionForValue {
                $this->validateArguments($arguments);

                return new IfConditionForValue((bool)$condition, $arguments['value']);
            },
            'Create if condition allowing usage of ".then()" and/or "else()"'
        );
    }

    private function multiply(): ExpressionFunctionInterface
    {
        return new ExpressionFunction(
            'multiply',
            function ($arguments, $value): int|float {
                $this->validateArguments($arguments);

                return $arguments['value'] * $value;
            },
            'Multiply value from input by the given argument'
        );
    }

    private function subtract(): ExpressionFunctionInterface
    {
        return new ExpressionFunction(
            'subtract',
            function ($arguments, $value): int|float {
                $this->validateArguments($arguments);

                return $arguments['value'] - $value;
            },
            'Subtract given argument from value from input'
        );
    }

    private function validateArguments(array $arguments): void
    {
        if (isset($arguments['value']) === false) {
            throw new MissingValueIndexException('Missing "value" in input');
        }
    }
}
