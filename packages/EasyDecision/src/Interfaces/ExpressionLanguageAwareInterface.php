<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

use StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface;

interface ExpressionLanguageAwareInterface
{
    /**
     * Set expression language.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface $expressionLanguage
     *
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void;
}
