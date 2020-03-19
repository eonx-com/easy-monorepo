<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

use EonX\EasyDecision\Expressions\ExpressionLanguage;

interface ExpressionLanguageFactoryInterface
{
    public function create(ExpressionLanguageConfigInterface $config): ExpressionLanguage;
}
