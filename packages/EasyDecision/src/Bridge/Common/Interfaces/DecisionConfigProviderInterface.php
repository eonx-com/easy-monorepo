<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common\Interfaces;

/**
 * @deprecated since 2.3.7
 */
interface DecisionConfigProviderInterface
{
    public function getDecisionType(): string;

    /**
     * @return null|mixed
     */
    public function getDefaultOutput();

    /**
     * @return null|mixed[]
     */
    public function getExpressionFunctionProviders(): ?array;

    /**
     * @return null|mixed[]
     */
    public function getExpressionFunctions(): ?array;

    /**
     * @return mixed[]
     */
    public function getRuleProviders(): array;
}
