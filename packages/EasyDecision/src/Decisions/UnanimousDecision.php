<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;

final class UnanimousDecision extends AbstractDecision
{
    /** @var bool */
    private $output = true;

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

        // If at least one false, decision output is false
        if ($output === false) {
            $this->output = false;
            // No need to keep processing rules because only one false is required to output false
            $context->stopPropagation();
        }
    }
}
