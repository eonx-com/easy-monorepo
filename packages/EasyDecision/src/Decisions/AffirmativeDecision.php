<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class AffirmativeDecision extends AbstractDecision
{
    private bool $output = false;

    protected function doHandleRuleOutput(mixed $output): void
    {
        // If at least one true, decision output is true
        if ((bool)$output === true) {
            $this->output = true;
            // No need to keep processing rules because only one true is required to output true
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
        $this->output = false;
    }
}
