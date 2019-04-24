<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Traits;

use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;

trait ExpressionLanguageAwareTrait
{
    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    private $expressionLanguage;

    /**
     * Set expression language.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface $expressionLanguage
     *
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }
}

\class_alias(
    ExpressionLanguageAwareTrait::class,
    'StepTheFkUp\EasyDecision\Traits\ExpressionLanguageAwareTrait',
    false
);
