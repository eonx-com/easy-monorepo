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
     * Get original input.
     *
     * @return mixed[]
     */
    public function getOriginalInput(): array;

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
     * Stop propagation, all rules after propagation has been stopped will be skipped.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     */
    public function stopPropagation(): self;
}


