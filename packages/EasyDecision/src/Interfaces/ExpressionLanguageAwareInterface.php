<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;

interface ExpressionLanguageAwareInterface
{
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void;
}
