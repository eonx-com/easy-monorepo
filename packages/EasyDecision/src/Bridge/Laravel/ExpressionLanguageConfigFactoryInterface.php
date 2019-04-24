<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Bridge\Laravel;

use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

interface ExpressionLanguageConfigFactoryInterface
{
    /**
     * Create expression language config for given decision.
     *
     * @param string $decision
     *
     * @return null|\LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function create(string $decision): ?ExpressionLanguageConfigInterface;
}

\class_alias(
    ExpressionLanguageConfigFactoryInterface::class,
    'StepTheFkUp\EasyDecision\Bridge\Laravel\ExpressionLanguageConfigFactoryInterface',
    false
);
