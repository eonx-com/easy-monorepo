<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

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

    public function __construct(WebhookBodyFormatterInterface $bodyFormatter, ?int $priority = null)
    {
        $this->bodyFormatter = $bodyFormatter;

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if (empty($webhook->getBody()) === false) {
            $webhook->mergeHttpClientOptions([
                'headers' => [
                    'Content-Type' => $this->bodyFormatter->getContentTypeHeader(),
                ],
                'body' => $this->bodyFormatter->format($webhook->getBody()),
            ]);
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
