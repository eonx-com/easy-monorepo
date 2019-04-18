<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

interface DecisionConfigInterface
{
    /**
     * Get decision type.
     *
     * @return string
     */
    public function getDecisionType(): string;

    /**
     * Get expression language config.
     *
     * @return null|\StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function getExpressionLanguageConfig(): ?ExpressionLanguageConfigInterface;

    /**
     * Get rules providers.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    public function getRuleProviders(): array;
}

\class_alias(
    DecisionConfigInterface::class,
    'LoyaltyCorp\EasyDecision\Interfaces\DecisionConfigInterface',
    false
);
