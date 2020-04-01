<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Interfaces;

/**
 * @deprecated since 2.3.7
 */
interface ExpressionFunctionProviderInterface
{
    /**
     * @return mixed[]
     */
    public function getFunctions(): array;
}
