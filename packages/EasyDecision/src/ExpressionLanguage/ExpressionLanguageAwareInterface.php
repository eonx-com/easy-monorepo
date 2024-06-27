<?php
declare(strict_types=1);

namespace EonX\EasyDecision\ExpressionLanguage;

interface ExpressionLanguageAwareInterface
{
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void;
}
