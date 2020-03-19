<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

interface ExpressionLanguageConfigInterface
{
    public function getBaseExpressionLanguage(): ?BaseExpressionLanguage;

    /**
     * @return null|mixed[]
     */
    public function getFunctionProviders(): ?array;

    /**
     * @return null|mixed[]
     */
    public function getFunctions(): ?array;
}
