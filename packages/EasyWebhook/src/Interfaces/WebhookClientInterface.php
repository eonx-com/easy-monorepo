<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookClientInterface
{
    public function configure(WebhookInterface $webhook): WebhookInterface;
    
    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface;
}
