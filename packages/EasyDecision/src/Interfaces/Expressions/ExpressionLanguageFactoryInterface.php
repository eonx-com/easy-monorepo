<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expressions;

use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguage;

interface ExpressionLanguageFactoryInterface
{
    /**
     * Create expression language for given config.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     *
     * @return \StepTheFkUp\EasyDecision\Expressions\ExpressionLanguage
     */
    public function create(ExpressionLanguageConfigInterface $config): ExpressionLanguage;
}

\class_alias(
    ExpressionLanguageFactoryInterface::class,
    'LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface',
    false
);
