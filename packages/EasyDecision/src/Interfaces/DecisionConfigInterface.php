<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

interface DecisionConfigInterface
{
    public function getDecisionType(): string;

    /**
     * @return null|mixed
     */
    public function getDefaultOutput();

    public function getExpressionLanguageConfig(): ?ExpressionLanguageConfigInterface;

    public function getName(): string;

    /**
     * @return null|mixed[]
     */
    public function getParams(): ?array;

    /**
     * @return \EonX\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    public function getRuleProviders(): array;
}
