<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionAggregatorInterface
{
    /**
     * @param \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[] $configurators
     *
     * @return \EonX\EasyDecision\DecisionAggregator
     */
    public function addDecision(DecisionInterface $decision, array $configurators): self;

    /**
     * @return \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[]
     */
    public function getConfiguratorsByDecision(DecisionInterface $decision): array;

    /**
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface[]
     */
    public function getDecisions(): array;
}
