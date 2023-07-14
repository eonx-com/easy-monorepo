<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Configurators;

use EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface;

abstract class AbstractClientConfigurator implements ClientConfiguratorInterface
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
