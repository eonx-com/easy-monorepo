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
     * @param mixed[]|null $params
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(string $decision, ?array $params = null): DecisionInterface;
}

\class_alias(
    DecisionFactoryInterface::class,
    'StepTheFkUp\EasyDecision\Bridge\Laravel\DecisionFactoryInterface',
    false
);
