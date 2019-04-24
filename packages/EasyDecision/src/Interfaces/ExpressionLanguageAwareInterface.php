<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;

interface ExpressionLanguageAwareInterface
{
    /**
     * Set expression language.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface $expressionLanguage
     *
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void;
}

\class_alias(
    ExpressionLanguageAwareInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\ExpressionLanguageAwareInterface',
    false
);
