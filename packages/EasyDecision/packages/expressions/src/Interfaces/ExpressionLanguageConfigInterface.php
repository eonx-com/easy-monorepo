<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Interfaces;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

/**
 * @deprecated since 2.3.7
 */
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
