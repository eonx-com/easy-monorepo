<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class ConsensusDecision extends AbstractDecision
{
    /** @var int */
    private $countFalse = 0;

    /** @var int */
    private $countTrue = 0;

    /**
     * Handle rule output.
     *
     * @param mixed $output
     *
     * @return void
     */
    protected function doHandleRuleOutput($output): void
    {
        (bool)$output ? $this->countTrue++ : $this->countFalse++;
    }

    /**
     * Let children classes make the decision.
     *
     * @return bool
     */
    protected function doMake(): bool
    {
        return $this->countTrue >= $this->countFalse;
    }

    /**
     * Get default output to return if no rules provided.
     *
     * @return bool
     */
    protected function getDefaultOutput(): bool
    {
        return true;
    }
}
