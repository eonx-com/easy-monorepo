<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

abstract class AbstractDecisionConfigurator implements DecisionConfiguratorInterface
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
}
