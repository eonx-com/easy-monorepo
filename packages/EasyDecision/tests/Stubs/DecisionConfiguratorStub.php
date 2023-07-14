<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RestrictedDecisionConfiguratorInterface;

final class DecisionConfiguratorStub implements DecisionConfiguratorInterface, RestrictedDecisionConfiguratorInterface
{
    public function __construct(
        private string $decisionName,
    ) {
    }

    public function configure(DecisionInterface $decision): void
    {
        // No body needed.
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function supports(DecisionInterface $decision): bool
    {
        return $decision->getName() === $this->decisionName;
    }
}
