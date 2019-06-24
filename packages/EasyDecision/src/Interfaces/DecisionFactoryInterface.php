<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

use Psr\Container\ContainerInterface;

interface DecisionFactoryInterface
{
    /**
     * Create decision for given config.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\DecisionConfigInterface $config
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(DecisionConfigInterface $config): DecisionInterface;

    /**
     * Set container.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container): void;
}

\class_alias(
    DecisionFactoryInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\DecisionFactoryInterface',
    false
);
