<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Bridge\Laravel;

use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;

interface DecisionFactoryInterface
{
    /**
     * Create decision for given decision name.
     *
     * @param string $decision
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(string $decision): DecisionInterface;
}

\class_alias(
    DecisionFactoryInterface::class,
    'StepTheFkUp\EasyDecision\Bridge\Laravel\DecisionFactoryInterface',
    false
);
