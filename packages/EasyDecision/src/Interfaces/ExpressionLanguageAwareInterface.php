<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;

interface ExpressionLanguageAwareInterface
{
    /**
     * Set expression language.
     *
     * @param \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface $expressionLanguage
     *
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void;
}
