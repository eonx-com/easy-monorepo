<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface DecisionFactoryInterface
{
    /**
     * Create decision for given config.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\DecisionConfigInterface $config
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(DecisionConfigInterface $config): DecisionInterface;
}

\class_alias(
    DecisionFactoryInterface::class,
    'LoyaltyCorp\EasyDecision\Interfaces\DecisionFactoryInterface',
    false
);
