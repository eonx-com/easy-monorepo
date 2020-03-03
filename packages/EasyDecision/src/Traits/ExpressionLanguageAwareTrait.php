<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Traits;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;

trait ExpressionLanguageAwareTrait
{
    /**
     * @var \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    private $expressionLanguage;

    /**
     * Set expression language.
     *
     * @param \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface $expressionLanguage
     *
     * @return void
     */
    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }
}
