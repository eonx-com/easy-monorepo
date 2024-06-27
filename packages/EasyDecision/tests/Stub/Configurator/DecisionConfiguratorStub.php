<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Configurator;

use EonX\EasyDecision\Configurator\DecisionConfiguratorInterface;
use EonX\EasyDecision\Configurator\RestrictedDecisionConfiguratorInterface;
use EonX\EasyDecision\Decision\DecisionInterface;

final class DecisionConfiguratorStub implements DecisionConfiguratorInterface, RestrictedDecisionConfiguratorInterface
{
    public function __construct(
        private string $decisionName,
    ) {
    }

    public function configure(DecisionInterface $decision): void
    {
        // No body needed
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
