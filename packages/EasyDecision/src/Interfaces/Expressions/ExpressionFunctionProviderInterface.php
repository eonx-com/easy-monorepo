<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionProviderInterface
{
    /**
     * @return mixed[]
     */
    public function getFunctions(): array;
}
