<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

abstract class AbstractClientConfigurator implements ClientConfiguratorInterface
{
    private readonly int $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
