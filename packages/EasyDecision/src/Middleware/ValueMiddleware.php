<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Middleware;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;
use LoyaltyCorp\EasyDecision\Traits\DealsWithValueInput;

final class ValueMiddleware extends AbstractMiddleware
{
    use DealsWithValueInput;

    /**
     * Make sure children classes handle given context.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     * @param mixed $output
     *
     * @return void
     */
    protected function doHandle(ContextInterface $context, $output): void
    {
        // If value decision, update the input for next middleware
        if ($context->getDecisionType() === DecisionInterface::TYPE_VALUE) {
            $this->addRuleOutput($context, $output);
            $this->updateInput($context, $output);
        }
    }
}

\class_alias(
    ValueMiddleware::class,
    'StepTheFkUp\EasyDecision\Middleware\ValueMiddleware',
    false
);
