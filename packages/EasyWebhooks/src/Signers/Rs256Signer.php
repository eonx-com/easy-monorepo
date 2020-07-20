<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Signers;

use EonX\EasyWebhooks\Interfaces\WebhookSignerInterface;

final class Rs256Signer implements WebhookSignerInterface
{
    public function sign(string $payload, string $secret): string
    {
        return \hash_hmac('sha256', $payload, $secret);
    }
}
