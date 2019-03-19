<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Decisions;

use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;

final class ValueDecision extends AbstractDecision
{
    /**
     * {@inheritdoc}
     */
    protected function createContext($input): ContextInterface
    {
        // If input is an array, index value must be set
        if (\is_array($input) && isset($input['value']) === false) {
            throw new InvalidArgumentException(\sprintf(
                'Passing an array input to %s require to set the index "value"',
                self::class
            ));
        }

        return parent::createContext($input);
    }

    /**
     * Do make decision based on given context.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    protected function doMake(ContextInterface $context): void
    {
        // Nothing to do for this type of decision
    }

    /**
     * Get decision type.
     *
     * @return string
     */
    protected function getDecisionType(): string
    {
        return DecisionInterface::TYPE_VALUE;
    }
}
