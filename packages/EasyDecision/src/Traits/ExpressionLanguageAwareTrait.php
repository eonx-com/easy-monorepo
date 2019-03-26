<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Traits;

use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;

trait ExpressionLanguageAwareTrait
{
    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    private $expressionLanguage;

    /**
     * Set expression language.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface $expressionLanguage
     *
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }
}
