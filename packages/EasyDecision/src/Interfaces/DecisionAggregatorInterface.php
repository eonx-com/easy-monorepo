<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionAggregatorInterface
{
    public function addDecision(DecisionInterface $decision): self;

    /**
     * @param \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[] $configurators
     */
    public function addDecisionConfigurators(DecisionInterface $decision, array $configurators): self;

    /**
     * @param \EonX\EasyDecision\Interfaces\RuleProviderInterface[] $ruleProviders
     */
    public function addDecisionRuleProviders(DecisionInterface $decision, array $ruleProviders): self;

    /**
     * @return \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[]
     */
    public function getConfiguratorsByDecision(DecisionInterface $decision): array;

    /**
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface[]
     */
    public function getDecisions(): array;

    /**
     * @return \EonX\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    public function getRuleProvidersByDecision(DecisionInterface $decision): array;
}
