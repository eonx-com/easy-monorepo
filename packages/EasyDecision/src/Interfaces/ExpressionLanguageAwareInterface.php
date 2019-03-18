<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;

interface ExpressionLanguageAwareInterface
{
    /**
     * Set expression language.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface $expressionLanguage
     *
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void;
}
