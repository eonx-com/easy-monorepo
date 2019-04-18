<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Traits;

use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;
use StepTheFkUp\EasyDecision\Interfaces\ValueInputInterface;

trait DealsWithValueInput
{
    /**
     * Update input on given context for given output for next rules to be able to proceed.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     * @param mixed $output
     *
     * @return void
     */
    private function updateInput(ContextInterface $context, $output): void
    {
        $input = $context->getInput();

        if (\is_array($input)) {
            $input['value'] = $output;
        }

        if ($input instanceof ValueInputInterface) {
            $input->setValue($output);
        }

        if ($input instanceof \stdClass) {
            $input->value = $output;
        }

        $context->setInput($input);
    }
}

\class_alias(
    DealsWithValueInput::class,
    'LoyaltyCorp\EasyDecision\Traits\DealsWithValueInput',
    false
);
