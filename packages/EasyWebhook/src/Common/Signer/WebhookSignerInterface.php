<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Signer;

interface WebhookSignerInterface
{
    public function sign(string $payload, string $secret): string;
}
