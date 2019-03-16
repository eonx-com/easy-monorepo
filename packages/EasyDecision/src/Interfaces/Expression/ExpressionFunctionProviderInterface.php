<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expression;

interface ExpressionFunctionProviderInterface
{
    /**
     * Get list of functions.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array;
}
