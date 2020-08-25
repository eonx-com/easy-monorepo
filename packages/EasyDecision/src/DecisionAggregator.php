<?php
declare(strict_types=1);

namespace EonX\EasyDecision;

use EonX\EasyDecision\Interfaces\DecisionAggregatorInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;

final class DecisionAggregator implements DecisionAggregatorInterface
{
    /**
     * @var mixed[]
     */
    private $configurators = [];

    /**
     * @var \EonX\EasyDecision\Interfaces\DecisionInterface[]
     */
    private $decisions = [];

    /**
     * @var mixed[]
     */
    private $ruleProviders = [];

    public function addDecision(DecisionInterface $decision): DecisionAggregatorInterface
    {
        $this->decisions[] = $decision;

        return $this;
    }

    public function addDecisionConfigurators(
        DecisionInterface $decision,
        array $configurators
    ): DecisionAggregatorInterface {
        $this->configurators[$this->getDecisionIdentifier($decision)] = $configurators;

        return $this;
    }

    public function addDecisionRuleProviders(
        DecisionInterface $decision,
        array $ruleProviders
    ): DecisionAggregatorInterface {
        $this->ruleProviders[$this->getDecisionIdentifier($decision)] = $ruleProviders;

        return $this;
    }

    public function getConfiguratorsByDecision(DecisionInterface $decision): array
    {
        return $this->configurators[$this->getDecisionIdentifier($decision)] ?? [];
    }

    public function getDecisions(): array
    {
        return $this->decisions;
    }

    public function getRuleProvidersByDecision(DecisionInterface $decision): array
    {
        return $this->configurators[$this->getDecisionIdentifier($decision)] ?? [];
    }

    private function getDecisionIdentifier(DecisionInterface $decision): string
    {
        return \spl_object_hash($decision);
    }
}
