<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use Psr\Container\ContainerInterface;

interface DecisionFactoryInterface
{
    /**
     * @deprecated since 2.3.7
     */
    public function create(DecisionConfigInterface $config): DecisionInterface;

    public function createAffirmativeDecision(?string $name = null): DecisionInterface;

    public function createByName(string $name): DecisionInterface;

    public function createConsensusDecision(?string $name = null): DecisionInterface;

    public function createUnanimousDecision(?string $name = null): DecisionInterface;

    public function createValueDecision(?string $name = null): DecisionInterface;

    /**
     * @return \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[]
     */
    public function getConfiguratorsByDecision(DecisionInterface $decision): array;

    /**
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface[]
     */
    public function getConfiguredDecisions(): array;

    /**
     * @deprecated since 2.3.7
     */
    public function setContainer(ContainerInterface $container): void;
}
