<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision;

use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;

final class Context implements ContextInterface
{
    /**
     * @var string
     */
    private $decisionType;

    /**
     * @var mixed
     */
    private $input;

    /**
     * @var mixed
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
     * Context constructor.
     *
     * @param string $decisionType
     * @param mixed $input
     */
    public function __construct(string $decisionType, $input)
    {
        $this->decisionType = $decisionType;
        $this->input = $input;
        $this->originalInput = $input;
    }

    /**
     * Add output for given rule.
     *
     * @param string $rule
     * @param mixed $output
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     */
    public function addRuleOutput(string $rule, $output): ContextInterface
    {
        $this->ruleOutputs[$rule] = $output;

        return $this;
    }

    /**
     * Get decision type.
     *
     * @return string
     */
    public function getDecisionType(): string
    {
        return $this->decisionType;
    }

    /**
     * Get input.
     *
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Get original input.
     *
     * @return mixed
     */
    public function getOriginalInput()
    {
        return $this->originalInput;
    }

    /**
     * Get all rules outputs in an associative array.
     *
     * @return mixed[]
     */
    public function getRuleOutputs(): array
    {
        return $this->ruleOutputs;
    }

    /**
     * Check if propagation stopped.
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Set input.
     *
     * @param mixed $input
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     */
    public function setInput($input): ContextInterface
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Stop propagation, all rules after propagation has been stopped will be skipped.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     */
    public function stopPropagation(): ContextInterface
    {
        $this->propagationStopped = true;

        return $this;
    }
}

\class_alias(
    Context::class,
    'LoyaltyCorp\EasyDecision\Context',
    false
);
