<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;
use LoyaltyCorp\EasyDecision\Middleware\YesNoMiddleware;

final class AffirmativeDecision extends AbstractDecision
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
            if ($output === true) {
                $context->setInput(true);

                return;
            }
        }

        $context->setInput(false);
    }

    /**
     * Get decision type.
     *
     * @return string
     */
    protected function getDecisionType(): string
    {
        return DecisionInterface::TYPE_YESNO_AFFIRMATIVE;
    }

    /**
     * Get middleware class.
     *
     * @return string
     */
    protected function getMiddlewareClass(): string
    {
        return YesNoMiddleware::class;
    }
}

\class_alias(
    AffirmativeDecision::class,
    'StepTheFkUp\EasyDecision\Decisions\AffirmativeDecision',
    false
);
