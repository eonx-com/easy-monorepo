<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\DataCollector;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Stack;
use EonX\EasyWebhook\WebhookClient;

final class TraceableWebhookClient implements WebhookClientInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    private array $results = [];

    public function __construct(
        private WebhookClientInterface $decorated,
    ) {
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\MiddlewareInterface[]
     */
    public function getMiddleware(): array
    {
        $stack = $this->decorated instanceof WebhookClient ? $this->decorated->getStack() : null;

        return $stack instanceof Stack ? $stack->getMiddleware() : [];
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
