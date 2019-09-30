<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Exceptions\MissingValueIndexException;
use LoyaltyCorp\EasyDecision\Helpers\IfConditionForValue;
use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;

final class ValueDecision extends AbstractDecision
{
    /** @var mixed */
    private $value;

    /**
     * {@inheritDoc}
     */
    public function make(array $input)
    {
        if (isset($input['value']) === false) {
            throw new MissingValueIndexException($this->getExceptionMessage(
                'missing "value" index in given input'
            ));
        }

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
        if ($output instanceof IfConditionForValue) {
            $output = $output->getValue();
        }

        // Log output
        $context->addRuleOutput($rule, $output);

        // Store local value
        $this->value = $output;

        // Update input for next rules with new value
        $this->updateInput(['value' => $output]);
    }
}

\class_alias(
    ValueDecision::class,
    'StepTheFkUp\EasyDecision\Decisions\ValueDecision',
    false
);
