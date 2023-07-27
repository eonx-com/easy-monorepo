<?php
declare(strict_types=1);

namespace EonX\EasyDecision;

use EonX\EasyDecision\Interfaces\ContextInterface;

final class Context implements ContextInterface
{
    private bool $propagationStopped = false;

    private array $ruleOutputs = [];

    public function __construct(
        private string $decisionType,
        private array $input,
    ) {
    }

    public function addRuleOutput(string $rule, mixed $output): ContextInterface
    {
        $this->ruleOutputs[$rule] = $output;

        return $this;
    }

    public function getDecisionType(): string
    {
        return $this->decisionType;
    }

    public function getOriginalInput(): array
    {
        return $this->input;
    }

    public function getRuleOutputs(): array
    {
        return $this->ruleOutputs;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): ContextInterface
    {
        $this->propagationStopped = true;

        return $this;
    }
}
