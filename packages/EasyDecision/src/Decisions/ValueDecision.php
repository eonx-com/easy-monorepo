<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Exceptions\MissingValueIndexException;
use EonX\EasyDecision\Interfaces\ContextInterface;

final class ValueDecision extends AbstractDecision
{
    /** @var mixed */
    private $value;

    /**
     * Make value decision for given array input.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function make(array $input)
    {
        $this->validateInputHasValue($input);

        // Store original value so even if no rule successful run we return at least the input value
        $this->value = $input['value'];

        return parent::make($input);
    }

    /**
     * Let children classes make the decision.
     *
     * @return mixed
     */
    protected function doMake()
    {
        return $this->value;
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
        $this->validateInputHasValue($input);

        return $input['value'];
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
        $value = $this->getOutputFromRule($rule, $output);

        // Log output
        $context->addRuleOutput($rule, $output);

        // Store local value
        $this->value = $value;

        // Update input for next rules with new value
        $this->updateInput(['value' => $value]);
    }

    /**
     * Validate that given input has value.
     *
     * @param mixed[] $input
     *
     * @return void
     *
     * @throws \EonX\EasyDecision\Exceptions\MissingValueIndexException If input doesn't contain value
     */
    private function validateInputHasValue(array $input): void
    {
        if (isset($input['value']) === false) {
            throw new MissingValueIndexException($this->getExceptionMessage(
                'missing "value" index in given input'
            ));
        }
    }
}
