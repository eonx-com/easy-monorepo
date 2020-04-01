<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Traits;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;

trait ExpressionLanguageAwareTrait
{
    /**
     * @var \EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface
     */
    private $expressionLanguage;

    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }
}
