<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces\Expressions;

interface ExpressionLanguageInterface
{
    /**
     * Add function to use in expressions.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface $function
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    public function addFunction(ExpressionFunctionInterface $function): self;

    /**
     * Evaluate given expression with given arguments and return output.
     *
     * @param string $expression
     * @param null|mixed[] $arguments
     *
     * @return mixed
     */
    public function evaluate(string $expression, ?array $arguments = null);

    /**
     * Get list of functions added.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array;

    /**
     * Validate given expression for given names.
     *
     * @param string $expression
     * @param null|string[] $names
     *
     * @return bool
     */
    public function validate(string $expression, ?array $names = null): bool;
}

\class_alias(
    ExpressionLanguageInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface',
    false
);
