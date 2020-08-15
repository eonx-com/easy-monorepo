<?php

declare(strict_types=1);

namespace EonX\EasyDecision;

use EonX\EasyDecision\Interfaces\DecisionContextInterface;

final class DecisionContext implements DecisionContextInterface
{
    /**
     * @var string
     */
    private $decisionType;

    /**
     * @var mixed[]
     */
    private $originalInput;

    /**
     * @var bool
     */
    private $propagationStopped = false;

    /**
     * @var mixed[]
     */
    private $ruleOutputs = [];

    /**
     * @param mixed[] $input
     */
    public function __construct(string $decisionType, array $input)
    {
        $this->decisionType = $decisionType;
        $this->originalInput = $input;
    }

    /**
     * @param mixed $output
     */
    public function addRuleOutput(string $rule, $output): DecisionContextInterface
    {
        $this->ruleOutputs[$rule] = $output;

        return $this;
    }

    public function getDecisionType(): string
    {
        return $this->decisionType;
    }

    /**
     * @return mixed[]
     */
    public function getOriginalInput(): array
    {
        return $this->originalInput;
    }

    /**
     * @return mixed[]
     */
    public function getRuleOutputs(): array
    {
        return $this->ruleOutputs;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): DecisionContextInterface
    {
        $this->propagationStopped = true;

        return $this;
    }
}
