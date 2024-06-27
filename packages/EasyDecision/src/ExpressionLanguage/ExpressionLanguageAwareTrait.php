<?php
declare(strict_types=1);

namespace EonX\EasyDecision\ExpressionLanguage;

trait ExpressionLanguageAwareTrait
{
    private ExpressionLanguageInterface $expressionLanguage;

    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }
}
