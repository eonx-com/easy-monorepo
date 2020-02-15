<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

interface DecisionConfigInterface
{
    /**
     * Get decision type.
     *
     * @return string
     */
    public function getDecisionType(): string;

    /**
     * Get default output if no rules provided.
     *
     * @return null|mixed
     */
    public function getDefaultOutput();

    /**
     * Get expression language config.
     *
     * @return null|\EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function getExpressionLanguageConfig(): ?ExpressionLanguageConfigInterface;

    /**
     * Get decision name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get additional params.
     *
     * @return null|mixed[]
     */
    public function getParams(): ?array;

    /**
     * Get rules providers.
     *
     * @return \EonX\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    public function getRuleProviders(): array;
}
