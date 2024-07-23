<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Context;

final class Context implements ContextInterface
{
    private bool $propagationStopped = false;

    private array $ruleOutputs = [];

    public function __construct(
        private readonly string $decisionType,
        private readonly array $input,
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
