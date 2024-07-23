<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decision;

final class UnanimousDecision extends AbstractDecision
{
    private bool $output = true;

    protected function doHandleRuleOutput(mixed $output): void
    {
        // If at least one false, decision output is false
        if ((bool)$output === false) {
            $this->output = false;
            // No need to keep processing rules because only one false is required to output false
            $this->getContext()
                ->stopPropagation();
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
