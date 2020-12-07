<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface IdAwareWebhookResultStoreInterface extends WebhookResultStoreInterface
{
    public function generateWebhookId(): string;
}
