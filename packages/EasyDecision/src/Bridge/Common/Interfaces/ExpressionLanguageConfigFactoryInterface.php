<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common\Interfaces;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageConfigInterface;

/**
 * @deprecated since 2.3.7
 */
interface ExpressionLanguageConfigFactoryInterface
{
    public function create(string $decision): ?ExpressionLanguageConfigInterface;
}
