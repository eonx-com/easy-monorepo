<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionFactoryInterface
{
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

    public function reset(): void;
}
