<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Factory;

use EonX\EasyDecision\Decision\DecisionInterface;

interface DecisionFactoryInterface
{
    public function createAffirmativeDecision(?string $name = null): DecisionInterface;

    public function createByName(string $name): DecisionInterface;

    public function createConsensusDecision(?string $name = null): DecisionInterface;

    public function createUnanimousDecision(?string $name = null): DecisionInterface;

    public function createValueDecision(?string $name = null): DecisionInterface;

    /**
     * @return \EonX\EasyDecision\Configurator\DecisionConfiguratorInterface[]
     */
    public function getConfiguratorsByDecision(DecisionInterface $decision): array;

    /**
     * @return \EonX\EasyDecision\Decision\DecisionInterface[]
     */
    public function getConfiguredDecisions(): array;

    public function reset(): void;
}
