<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\DataCollector;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\WebhookClient;

final class TraceableWebhookClient implements WebhookClientInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $decorated;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    private $results = [];

    public function __construct(WebhookClientInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function configure(WebhookInterface $webhook): WebhookInterface
    {
        return $this->decorated->configure($webhook);
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookConfiguratorInterface[]
     */
    public function getConfigurators(): array
    {
        return $this->decorated instanceof WebhookClient ? $this->decorated->getConfigurators() : [];
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        $result = $this->decorated->sendWebhook($webhook);

        $this->results[] = $result;

        return $result;
    }
}
