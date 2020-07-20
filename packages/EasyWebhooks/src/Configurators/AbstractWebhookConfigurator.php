<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Configurators;

use EonX\EasyWebhooks\Interfaces\WebhookConfiguratorInterface;

abstract class AbstractWebhookConfigurator implements WebhookConfiguratorInterface
{
    /**
     * @var int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
