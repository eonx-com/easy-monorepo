<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

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
     * @return null|\LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function getExpressionLanguageConfig(): ?ExpressionLanguageConfigInterface;

    /**
     * Get additional params.
     *
     * @return null|mixed[]
     */
    public function getParams(): ?array;

    /**
     * Get rules providers.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    public function getRuleProviders(): array;
}

\class_alias(
    DecisionConfigInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\DecisionConfigInterface',
    false
);
