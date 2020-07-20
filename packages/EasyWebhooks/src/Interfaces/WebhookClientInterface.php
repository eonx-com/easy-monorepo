<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Interfaces;

interface WebhookClientInterface
{
    public function sendWebhook(WebhookInterface $data): void;
}
