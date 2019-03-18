<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Traits;

use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Interfaces\ContextAwareInterface;
use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;

trait DealsWithContextTrait
{
    /**
     * Make sure to give context to input if it can deal with it.
     *
     * @param ContextInterface $context
     *
     * @return void
     */
    private function handleContextAwareInputs(ContextInterface $context): void
    {
        $input = $context->getInput();

        // Give context to input to be able to stop propagation from expression rules
        if ($input instanceof ContextAwareInterface) {
            $input->setContext($context);
        }

        // If input is an array add context to it
        if (\is_array($input)) {
            // Index context cannot be used by users to avoid unexpected behaviours
            if (isset($input['context'])) {
                throw new InvalidArgumentException(
                    'When giving an array input to a decision, "context" is a reserved index it cannot be used'
                );
            }

            $input['context'] = $context;
        }

        $context->setInput($input);
    }

    /**
     * Remove context from given context's input.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    private function removeContextFromInput(ContextInterface $context): void
    {
        $context->setInput($this->removeContextFromOutput($context->getInput()));
    }

    /**
     * Remove context from given output.
     *
     * @param mixed $output
     *
     * @return mixed
     */
    private function removeContextFromOutput($output)
    {
        if (\is_array($output)) {
            unset($output['context']);
        }

        return $output;
    }
}
