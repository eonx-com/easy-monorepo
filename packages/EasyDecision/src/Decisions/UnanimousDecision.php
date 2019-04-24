<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;

final class UnanimousDecision extends AbstractDecision
{
    /**
     * Do make decision based on given context.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    protected function doMake(ContextInterface $context): void
    {
        foreach ($context->getRuleOutputs() as $output) {
            if ($output === false) {
                $context->setInput(false);

                return;
            }
        }

        $context->setInput(true);
    }

    /**
     * Get decision type.
     *
     * @return string
     */
    protected function getDecisionType(): string
    {
        return DecisionInterface::TYPE_YESNO_UNANIMOUS;
    }
}

\class_alias(
    UnanimousDecision::class,
    'StepTheFkUp\EasyDecision\Decisions\UnanimousDecision',
    false
);
