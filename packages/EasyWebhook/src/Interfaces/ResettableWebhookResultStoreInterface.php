<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface ResettableWebhookResultStoreInterface extends WebhookResultStoreInterface
{
    public function reset(): void;
}
