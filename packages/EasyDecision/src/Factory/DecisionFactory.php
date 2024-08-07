<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Factory;

use EonX\EasyDecision\Configurator\DecisionConfiguratorInterface;
use EonX\EasyDecision\Configurator\RestrictedDecisionConfiguratorInterface;
use EonX\EasyDecision\Decision\AffirmativeDecision;
use EonX\EasyDecision\Decision\ConsensusDecision;
use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\Decision\UnanimousDecision;
use EonX\EasyDecision\Decision\ValueDecision;
use EonX\EasyDecision\Provider\MappingProviderInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;

final class DecisionFactory implements DecisionFactoryInterface
{
    /**
     * @var \EonX\EasyDecision\Configurator\DecisionConfiguratorInterface[]
     */
    private readonly array $configurators;

    /**
     * @var \EonX\EasyDecision\Decision\DecisionInterface[]
     */
    private array $configuredDecisions = [];

    /**
     * @var array<string, \EonX\EasyDecision\Configurator\DecisionConfiguratorInterface[]>
     */
    private array $decisionConfigurators = [];

    public function __construct(
        private readonly MappingProviderInterface $mappingProvider,
        ?iterable $configurators = null,
    ) {
        /** @var \EonX\EasyDecision\Configurator\DecisionConfiguratorInterface[] $filteredAndSortedConfigurators */
        $filteredAndSortedConfigurators = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($configurators ?? [], DecisionConfiguratorInterface::class)
        );
        $this->configurators = $filteredAndSortedConfigurators;
    }

    public function createAffirmativeDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new AffirmativeDecision($name));
    }

    public function createByName(string $name): DecisionInterface
    {
        $decisionClass = $this->mappingProvider->getDecisionType($name);
        /** @var \EonX\EasyDecision\Decision\DecisionInterface $decision */
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
     * @param \EonX\EasyDecision\Configurator\DecisionConfiguratorInterface[] $configurators
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
