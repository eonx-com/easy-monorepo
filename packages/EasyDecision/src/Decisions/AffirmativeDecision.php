<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class AffirmativeDecision extends AbstractDecision
{
    /** @var bool */
    private $output = false;

    /**
     * Handle rule output.
     *
     * @param mixed $output
     *
     * @return void
     */
    protected function doHandleRuleOutput($output): void
    {
        // If at least one true, decision output is true
        if ((bool)$output === true) {
            $this->output = true;
            // No need to keep processing rules because only one true is required to output true
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
