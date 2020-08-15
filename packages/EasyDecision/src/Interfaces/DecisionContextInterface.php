<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionContextInterface
{
    /**
     * @param mixed $output
     */
    public function addRuleOutput(string $rule, $output): self;

    public function getDecisionType(): string;

    /**
     * @return mixed[]
     */
    public function getOriginalInput(): array;

    /**
     * @return mixed[]
     */
    public function getRuleOutputs(): array;

    public function isPropagationStopped(): bool;

    public function stopPropagation(): self;
}
