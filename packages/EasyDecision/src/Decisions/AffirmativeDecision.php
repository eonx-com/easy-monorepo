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
     * Get default output to return if no rules provided.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    protected function getDefaultOutput(array $input)
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
        $value = (bool)$this->getOutputFromRule($rule, $output);

        // Log output
        $context->addRuleOutput($rule, $output);

        // If at least one true, decision output is true
        if ($value === true) {
            $this->output = true;
            // No need to keep processing rules because only one true is required to output true
            $context->stopPropagation();
        }
    }
}
