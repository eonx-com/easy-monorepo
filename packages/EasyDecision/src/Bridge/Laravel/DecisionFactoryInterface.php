<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Bridge\Laravel;

use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;

interface DecisionFactoryInterface
{
    /**
     * Create decision for given decision name.
     *
     * @param string $decision
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(string $decision): DecisionInterface;
}

\class_alias(
    DecisionFactoryInterface::class,
    'LoyaltyCorp\EasyDecision\Bridge\Laravel\DecisionFactoryInterface',
    false
);
