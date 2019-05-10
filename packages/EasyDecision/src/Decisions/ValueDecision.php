<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Exceptions\MissingValueIndexException;
use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;

final class ValueDecision extends AbstractDecision
{
    /**
     * {@inheritdoc}
     */
    protected function createContext($input): ContextInterface
    {
        // If input is an array, index value must be set
        if (\is_array($input) && isset($input['value']) === false) {
            throw new MissingValueIndexException(\sprintf(
                'Passing an array input to %s require to set the index "value"',
                self::class
            ));
        }

        // If input is a stdClass, property value must be set
        if ($input instanceof \stdClass && isset($input->value) === false) {
            throw new MissingValueIndexException(\sprintf(
                'Passing a stdClass input to %s require to set the property "value"',
                self::class
            ));
        }

        return parent::createContext($input);
    }

    /**
     * Do make decision based on given context.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
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

\class_alias(
    ValueDecision::class,
    'StepTheFkUp\EasyDecision\Decisions\ValueDecision',
    false
);
