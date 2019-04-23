<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Middleware;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;

final class YesNoMiddleware extends AbstractMiddleware
{
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
        // If yes/no decision, add rule output as boolean
        if (\in_array($context->getDecisionType(), DecisionInterface::TYPES_YESNO)) {
            $this->addRuleOutput($context, (bool)$output);

            // If yes/no affirmative decision, stop propagation
            if ($context->getDecisionType() === DecisionInterface::TYPE_YESNO_AFFIRMATIVE && (bool)$output) {
                $context->stopPropagation();
            }
        }
    }
}

\class_alias(
    YesNoMiddleware::class,
    'StepTheFkUp\EasyDecision\Middleware\YesNoMiddleware',
    false
);
