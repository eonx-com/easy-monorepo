<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Formatter\JsonWebhookBodyFormatter;
use EonX\EasyWebhook\Common\Formatter\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class BodyFormatterMiddleware extends AbstractConfigureOnceMiddleware
{
    private readonly JsonWebhookBodyFormatter $jsonFormatter;

    public function __construct(
        private readonly WebhookBodyFormatterInterface $bodyFormatter,
        ?int $priority = null,
    ) {
        $this->jsonFormatter = new JsonWebhookBodyFormatter();

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $httpClientOptions = $webhook->getHttpClientOptions() ?? [];
        $json = $httpClientOptions['json'] ?? null;

        // Allow to use "json" http client option
        if (\is_array($json) && \count($json) > 0) {
            $this->updateWebhook(
                $webhook,
                $this->jsonFormatter->format($json),
                $this->jsonFormatter->getContentTypeHeader()
            );
        }

        $bodyAsString = $webhook->getBodyAsString() ?? '';
        $body = $webhook->getBody() ?? [];

        // Body set as string has priority
        if ($bodyAsString === '' && \count($body) > 0) {
            $this->updateWebhook(
                $webhook,
                $this->bodyFormatter->format($body),
                $this->bodyFormatter->getContentTypeHeader()
            );
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }

    private function updateWebhook(WebhookInterface $webhook, string $formatted, string $header): void
    {
        $webhook->bodyAsString($formatted);

        $webhook->mergeHttpClientOptions([
            'body' => $formatted,
            'headers' => [
                'Content-Type' => $header,
            ],
        ]);
    }
}
