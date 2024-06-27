<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Signer;

final class Rs256WebhookSigner implements WebhookSignerInterface
{
    public function sign(string $payload, string $secret): string
    {
        return \hash_hmac('sha256', $payload, $secret);
    }
}
