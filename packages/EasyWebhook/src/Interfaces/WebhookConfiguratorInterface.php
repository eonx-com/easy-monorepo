<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookConfiguratorInterface
{
    /**
     * @var string
     */
    public const HEADER_EVENT = 'X-Webhook-Event';

    /**
     * @var string
     */
    public const HEADER_ID = 'X-Webhook-Id';

    /**
     * @var string
     */
    public const HEADER_SIGNATURE = 'X-Webhook-Signature';

    public function configure(WebhookInterface $webhook): void;

    public function getPriority(): int;
}
