<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Interfaces\ContextInterface;

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
     * Get default output to return if no rules provided.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    protected function getDefaultOutput(array $input): bool
    {
        return true;
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

        // If at least one false, decision output is false
        if ($output === false) {
            $this->output = false;
            // No need to keep processing rules because only one false is required to output false
            $context->stopPropagation();
        }
    }
}
