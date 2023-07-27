<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;

abstract class AbstractConfigurator implements DecisionConfiguratorInterface
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
