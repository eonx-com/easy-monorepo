<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Traits;

use StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface;

trait ExpressionLanguageAwareTrait
{
    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface
     */
    private $expressionLanguage;

    /**
     * Set expression language.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface $expressionLanguage
     *
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }
}
