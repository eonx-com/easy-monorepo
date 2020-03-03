<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Exceptions\MissingValueIndexException;

final class ValueDecision extends AbstractDecision
{
    /**
     * Make value decision for given array input.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function make(array $input)
    {
        if (isset($input['value']) === false) {
            throw new MissingValueIndexException($this->getExceptionMessage(
                'missing "value" index in given input'
            ));
        }

        return parent::make($input);
    }

    /**
     * Handle rule output.
     *
     * @param \EonX\EasyDecision\Interfaces\ContextInterface $context
     * @param mixed $output
     *
     * @return void
     */
    protected function doHandleRuleOutput($output): void
    {
        // Update input for next rules with new value
        $this->input['value'] = $output;
    }

    /**
     * Let children classes make the decision.
     *
     * @return mixed
     */
    protected function doMake()
    {
        return $this->input['value'];
    }

    /**
     * Get default output to return if no rules provided.
     *
     * @return mixed
     */
    protected function getDefaultOutput()
    {
        return $this->input['value'];
    }
}
