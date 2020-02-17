<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Interfaces\ContextInterface;

final class ConsensusDecision extends AbstractDecision
{
    /** @var int */
    private $countFalse = 0;

    /** @var int */
    private $countTrue = 0;

    /**
     * Let children classes make the decision.
     *
     * @return mixed
     */
    protected function doMake()
    {
        return $this->countTrue >= $this->countFalse;
    }

    /**
     * Get default output to return if no rules provided.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    protected function getDefaultOutput(array $input)
    {
        return true;
    }

    /**
     * Handle rule output.
     *
     * @param \EonX\EasyDecision\Interfaces\ContextInterface $context
     * @param string $rule
     * @param mixed $output
     *
     * @return void
     */
    protected function handleRuleOutput(ContextInterface $context, string $rule, $output): void
    {
        // Convert output to boolean
        $value = (bool)$this->getOutputFromRule($rule, $output);

        // Log output
        $context->addRuleOutput($rule, $output);

        // Count true
        if ($value === true) {
            $this->countTrue++;

            return;
        }

        // Count false
        if ($value === false) {
            $this->countFalse++;
        }
    }
}
