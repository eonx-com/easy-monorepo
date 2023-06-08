<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\MappingProviderInterface;
use EonX\EasyDecision\Interfaces\RestrictedDecisionConfiguratorInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class DecisionFactory implements DecisionFactoryInterface
{
    /**
     * @var \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \EonX\EasyDecision\Interfaces\DecisionInterface[]
     */
    private $configuredDecisions = [];

    /**
     * @var mixed[]
     */
    private $decisionConfigurators = [];

    /**
     * @var \EonX\EasyDecision\Interfaces\MappingProviderInterface
     */
    private $mappingProvider;

    /**
     * @param null|iterable<mixed> $configurators
     */
    public function __construct(MappingProviderInterface $mappingProvider, ?iterable $configurators = null)
    {
        $this->mappingProvider = $mappingProvider;

        $this->configurators = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($configurators ?? [], DecisionConfiguratorInterface::class)
        );
    }

    public function createAffirmativeDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new AffirmativeDecision($name));
    }

    public function createByName(string $name): DecisionInterface
    {
        $decisionClass = $this->mappingProvider->getDecisionType($name);
        /** @var \EonX\EasyDecision\Interfaces\DecisionInterface $decision */
        $decision = new $decisionClass($name);

        return $this->configureDecision($decision);
    }

    public function createConsensusDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new ConsensusDecision($name));
    }

    public function createUnanimousDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new UnanimousDecision($name));
    }

    public function createValueDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new ValueDecision($name));
    }

    public function getConfiguratorsByDecision(DecisionInterface $decision): array
    {
        return $this->decisionConfigurators[\spl_object_hash($decision)] ?? [];
    }

    public function getConfiguredDecisions(): array
    {
        return $this->configuredDecisions;
    }

    public function reset(): void
    {
        $this->decisionConfigurators = [];
        $this->configuredDecisions = [];
    }

    /**
     * @param \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[] $configurators
     */
    private function addConfiguredDecision(DecisionInterface $decision, array $configurators): void
    {
        $this->decisionConfigurators[\spl_object_hash($decision)] = $configurators;
        $this->configuredDecisions[] = $decision;
    }

    private function configureDecision(DecisionInterface $decision): DecisionInterface
    {
        $decisionConfigs = [];

        foreach ($this->configurators as $configurator) {
            if ($configurator instanceof RestrictedDecisionConfiguratorInterface
                && $configurator->supports($decision) === false) {
                continue;
            }

            $configurator->configure($decision);

            $decisionConfigs[] = $configurator;
        }

        $this->addConfiguredDecision($decision, $decisionConfigs);

        return $decision;
    }
}
