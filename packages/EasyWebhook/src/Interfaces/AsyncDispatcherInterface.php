<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface AsyncDispatcherInterface
{
    public function dispatch(WebhookInterface $webhook): void;
}
