<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class UnanimousDecision extends AbstractDecision
{
    /**
     * @var bool
     */
    private $output = true;

    /**
     * @param mixed $output
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

    protected function doMake(): bool
    {
        return $this->output;
    }

    protected function getDefaultOutput(): bool
    {
        return true;
    }

    protected function reset(): void
    {
        $this->output = true;
    }
}
