<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common\Interfaces;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

interface ExpressionLanguageConfigFactoryInterface
{
    public function create(string $decision): ?ExpressionLanguageConfigInterface;
}
