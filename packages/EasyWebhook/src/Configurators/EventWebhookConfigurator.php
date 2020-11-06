<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Configurators;

use EonX\EasyWebhook\Interfaces\WebhookInterface;

final class EventWebhookConfigurator extends AbstractWebhookConfigurator
{
    /**
     * @var string
     */
    private $eventHeader;

    public function __construct(?string $eventHeader = null, ?int $priority = null)
    {
        $this->eventHeader = $eventHeader ?? 'X-Event';

        parent::__construct($priority);
    }

    public function configure(WebhookInterface $webhook): void
    {
        if (empty($webhook->getEvent())) {
            return;
        }

        $webhook->mergeHttpClientOptions([
            'headers' => [
                $this->eventHeader => $webhook->getEvent(),
            ],
        ]);
    }
}
