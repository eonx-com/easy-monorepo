<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common\Interfaces;

interface DecisionConfigProviderInterface
{
    /**
     * Get decision type.
     *
     * @return string
     */
    public function getDecisionType(): string;

    /**
     * Get decision default output.
     *
     * @return null|mixed
     */
    public function getDefaultOutput();

    /**
     * Get expression functions providers list.
     *
     * @return null|mixed[]
     */
    public function getExpressionFunctionProviders(): ?array;

    /**
     * Get expression functions list.
     *
     * @return null|mixed[]
     */
    public function getExpressionFunctions(): ?array;

    /**
     * Get rule providers. Can be an instance or service locator.
     *
     * @return mixed[]
     */
    public function getRuleProviders(): array;
}
