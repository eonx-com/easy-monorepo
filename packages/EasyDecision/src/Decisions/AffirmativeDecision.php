<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

final class AffirmativeDecision extends AbstractDecision
{
    /**
     * @var bool
     */
    private $output = false;

    /**
     * @param mixed $output
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

    protected function doMake(): bool
    {
        return $this->output;
    }

    protected function getDefaultOutput(): bool
    {
        return true;
    }
}
