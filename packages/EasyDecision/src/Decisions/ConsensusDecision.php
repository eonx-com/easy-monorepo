<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;

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
     * Handle rule output.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     * @param string $rule
     * @param mixed $output
     *
     * @return void
     */
    protected function handleRuleOutput(ContextInterface $context, string $rule, $output): void
    {
        // Convert output to boolean
        $output = (bool)$output;

        // Log output
        $context->addRuleOutput($rule, $output);

        // Count true
        if ($output === true) {
            $this->countTrue++;

            return;
        }

        // Count false
        if ($output === false) {
            $this->countFalse++;
        }
    }
}
