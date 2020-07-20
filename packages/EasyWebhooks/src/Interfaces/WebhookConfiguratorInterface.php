<?php
declare(strict_types=1);

namespace EonX\EasyWebhooks\Interfaces;

interface WebhookConfiguratorInterface
{
    public function getPriority(): int;

    public function configure(WebhookInterface $webhook): void;
}
