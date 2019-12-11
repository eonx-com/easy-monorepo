<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Interfaces\ContextInterface;

final class AffirmativeDecision extends AbstractDecision
{
    /** @var bool */
    private $output = false;

    /**
     * Let children classes make the decision.
     *
     * @return mixed
     */
    protected function doMake()
    {
        return $this->output;
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
        $output = (bool)$output;

        // Log output
        $context->addRuleOutput($rule, $output);

        // If at least one true, decision output is true
        if ($output === true) {
            $this->output = true;
            // No need to keep processing rules because only one true is required to output true
            $context->stopPropagation();
        }
    }
}
