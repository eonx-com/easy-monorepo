<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Signers;

use EonX\EasyWebhook\Interfaces\WebhookSignerInterface;

final class Rs256Signer implements WebhookSignerInterface
{
    public function sign(string $payload, string $secret): string
    {
        return \hash_hmac('sha256', $payload, $secret);
    }
}
