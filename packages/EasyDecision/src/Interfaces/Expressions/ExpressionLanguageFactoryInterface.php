<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces\Expressions;

use LoyaltyCorp\EasyDecision\Expressions\ExpressionLanguage;

interface ExpressionLanguageFactoryInterface
{
    /**
     * Create expression language for given config.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     *
     * @return \LoyaltyCorp\EasyDecision\Expressions\ExpressionLanguage
     */
    public function create(ExpressionLanguageConfigInterface $config): ExpressionLanguage;
}


