<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class UnanimousDecision extends AbstractDecision
{
    /** @var bool */
    private $output = true;

    /**
     * Handle rule output.
     *
     * @param mixed $output
     *
     * @return void
     */
    protected function doHandleRuleOutput($output): void
    {
        // If at least one false, decision output is false
        if ((bool)$output === false) {
            $this->output = false;
            // No need to keep processing rules because only one false is required to output false
            $this->context->stopPropagation();
        }
    }

    /**
     * Let children classes make the decision.
     *
     * @return bool
     */
    protected function doMake(): bool
    {
        return $this->output;
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
