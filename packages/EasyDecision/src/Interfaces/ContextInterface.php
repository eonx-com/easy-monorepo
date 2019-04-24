<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface ContextInterface
{
    /**
     * Add output for given rule.
     *
     * @param string $rule
     * @param mixed $output
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     */
    public function addRuleOutput(string $rule, $output): self;

    /**
     * Get decision type.
     *
     * @return string
     */
    public function getDecisionType(): string;

    /**
     * Get input.
     *
     * @return mixed
     */
    public function getInput();

    /**
     * Get original input.
     *
     * @return mixed
     */
    public function getOriginalInput();

    /**
     * Get all rules outputs in an associative array.
     *
     * @return mixed[]
     */
    public function getRuleOutputs(): array;

    /**
     * Check if propagation stopped.
     *
     * @return bool
     */
    public function isPropagationStopped(): bool;

    /**
     * Set input.
     *
     * @param mixed $input
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     */
    public function setInput($input): self;

    /**
     * Stop propagation, all rules after propagation has been stopped will be skipped.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     */
    public function stopPropagation(): self;
}

\class_alias(
    ContextInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\ContextInterface',
    false
);
