<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Client;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\Stack;

final class TraceableWebhookClient implements WebhookClientInterface
{
    /**
     * @var \EonX\EasyWebhook\Common\Entity\WebhookResultInterface[]
     */
    private array $results = [];

    public function __construct(
        private WebhookClientInterface $decorated,
    ) {
    }

    /**
     * @return \EonX\EasyWebhook\Common\Middleware\MiddlewareInterface[]
     */
    public function getMiddleware(): array
    {
        $stack = $this->decorated instanceof WebhookClient ? $this->decorated->getStack() : null;

        return $stack instanceof Stack ? $stack->getMiddleware() : [];
    }

    /**
     * @return \EonX\EasyWebhook\Common\Entity\WebhookResultInterface[]
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
