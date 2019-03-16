<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expression;

use StepTheFkUp\EasyDecision\Interfaces\ExpressionFunctionInterface;

interface ExpressionLanguageInterface
{
    /**
     * Add function to use in expressions.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ExpressionFunctionInterface $function
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface
     */
    public function addFunction(ExpressionFunctionInterface $function): self;

    /**
     * Add function provider to add multiple functions at once.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionFunctionProviderInterface $provider
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface
     */
    public function addFunctionProvider(ExpressionFunctionProviderInterface $provider): self;

    /**
     * Evaluate given expression with given arguments and return output.
     *
     * @param string $expression
     * @param null|mixed[] $arguments
     *
     * @return mixed
     */
    public function evaluate(string $expression, ?array $arguments = null);
}
