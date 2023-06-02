<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Formatters\JsonFormatter;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class BodyFormatterMiddleware extends AbstractConfigureOnceMiddleware
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface
     */
    private $bodyFormatter;

    /**
     * @var \EonX\EasyWebhook\Formatters\JsonFormatter
     */
    private $jsonFormatter;

    public function __construct(WebhookBodyFormatterInterface $bodyFormatter, ?int $priority = null)
    {
        $this->bodyFormatter = $bodyFormatter;
        $this->jsonFormatter = new JsonFormatter();

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

        // Body set as string has priority
        if (empty($webhook->getBodyAsString()) && empty($webhook->getBody()) === false) {
            $this->updateWebhook(
                $webhook,
                $this->bodyFormatter->format($webhook->getBody()),
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
            'headers' => [
                'Content-Type' => $header,
            ],
            'body' => $formatted,
        ]);
    }
}
