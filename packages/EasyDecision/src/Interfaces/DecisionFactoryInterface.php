<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface DecisionFactoryInterface
{
    /**
     * Create decision for given config.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\DecisionConfigInterface $config
     * @param mixed[]|null $params
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(DecisionConfigInterface $config, ?array $params = null): DecisionInterface;
}

\class_alias(
    DecisionFactoryInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\DecisionFactoryInterface',
    false
);
