<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface ContextInterface
{
    public function addRuleOutput(string $rule, mixed $output): self;

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
