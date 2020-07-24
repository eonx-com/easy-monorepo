<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Configurators;

use EonX\EasyWebhook\Interfaces\WebhookInterface;

final class MethodWebhookConfigurator extends AbstractWebhookConfigurator
{
    /**
     * @var string
     */
    private $method;

    public function __construct(?string $method = null, ?int $priority = null)
    {
        $this->method = $method ?? WebhookInterface::DEFAULT_METHOD;

        parent::__construct($priority);
    }

    public function configure(WebhookInterface $webhook): void
    {
        if (empty($webhook->getMethod()) === false) {
            return;
        }

        $webhook->method($this->method);
    }
}
