<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\DataCollector;

use EonX\EasyUtils\Common\DataCollector\AbstractDataCollector;
use EonX\EasyWebhook\Common\Client\TraceableWebhookClient;
use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class WebhookDataCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly WebhookClientInterface $webhookClient,
    ) {
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->setMiddleware();
        $this->setResults();
    }

    public function getMiddleware(): array
    {
        return $this->data['webhook_middleware'] ?? [];
    }

    /**
     * @return \EonX\EasyWebhook\Common\Entity\WebhookResultInterface[]
     */
    public function getResults(): array
    {
        return $this->data['webhook_results'] ?? [];
    }

    private function setMiddleware(): void
    {
        $this->data['webhook_configurators'] = [];

        if (($this->webhookClient instanceof TraceableWebhookClient) === false) {
            return;
        }

        foreach ($this->webhookClient->getMiddleware() as $middleware) {
            $reflection = new ReflectionClass($middleware);

            $this->data['webhook_middleware'][] = [
                'class' => $reflection->getName(),
                'filename' => $reflection->getFileName(),
                'priority' => $middleware->getPriority(),
            ];
        }
    }

    private function setResults(): void
    {
        $this->data['webhook_results'] = [];

        if (($this->webhookClient instanceof TraceableWebhookClient) === false) {
            return;
        }

        $map = static fn (WebhookResultInterface $result): array => [
            'response' => $result->getResponse() !== null ? $result->getResponse()
                ->getInfo() : null,
            'throwable' => $result->getThrowable(),
            'webhook' => $result->getWebhook(),
        ];

        $this->data['webhook_results'] = \array_map($map, $this->webhookClient->getResults());
    }
}
