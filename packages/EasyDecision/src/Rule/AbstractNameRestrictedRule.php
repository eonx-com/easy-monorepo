<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Rule;

use EonX\EasyDecision\Decision\DecisionInterface;

abstract class AbstractNameRestrictedRule implements RestrictedRuleInterface
{
    private int $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function supportsDecision(DecisionInterface $decision): bool
    {
        return $decision->getName() === $this->getDecisionName();
    }

    abstract protected function getDecisionName(): string;
}
