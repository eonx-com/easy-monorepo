<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionProviderInterface
{
    /**
     * Get list of functions.
     *
     * @return mixed[]
     */
    public function getFunctions(): array;
}

\class_alias(
    ExpressionFunctionProviderInterface::class,
    'LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface',
    false
);
