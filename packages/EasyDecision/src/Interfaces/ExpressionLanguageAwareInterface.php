<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;

interface ExpressionLanguageAwareInterface
{
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void;
}
