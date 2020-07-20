<?php
declare(strict_types=1);

namespace EonX\EasyWebhooks\Interfaces;

interface WebhookSignerInterface
{
    public function sign(string $payload, string $secret): string;
}
