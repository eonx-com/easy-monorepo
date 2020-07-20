<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Configurators;

use EonX\EasyWebhooks\Interfaces\WebhookBodyFormatterInterface;
use EonX\EasyWebhooks\Interfaces\WebhookInterface;

final class BodyFormatterWebhookConfigurator extends AbstractWebhookConfigurator
{
    /**
     * @var \EonX\EasyWebhooks\Interfaces\WebhookBodyFormatterInterface
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
