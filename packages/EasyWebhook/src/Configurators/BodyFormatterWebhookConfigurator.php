<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Configurators;

use EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;

final class BodyFormatterWebhookConfigurator extends AbstractWebhookConfigurator
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

    public function configure(WebhookInterface $webhook): void
    {
        if (empty($webhook->getBody())) {
            return;
        }

        $webhook->mergeHttpClientOptions([
            'headers' => [
                'Content-Type' => $this->bodyFormatter->getContentTypeHeader(),
            ],
            'body' => $this->bodyFormatter->format($webhook->getBody()),
        ]);
    }
}
