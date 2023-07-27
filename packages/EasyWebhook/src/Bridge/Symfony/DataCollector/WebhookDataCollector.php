<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\DataCollector;

use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

final class WebhookDataCollector extends DataCollector
{
    public const NAME = 'easy_webhook.data_collector';

    public function __construct(
        private WebhookClientInterface $webhookClient,
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

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    public function getResults(): array
    {
        return $this->data['webhook_results'] ?? [];
    }

    public function reset(): void
    {
        $this->data = [];
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
