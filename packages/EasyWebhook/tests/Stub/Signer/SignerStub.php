<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stub\Signer;

use EonX\EasyWebhook\Common\Signer\WebhookSignerInterface;

final class SignerStub implements WebhookSignerInterface
{
    private ?string $payload = null;

    private ?string $secret = null;

    private readonly string $signature;

    public function __construct(?string $signature = null)
    {
        $this->signature = $signature ?? 'my-signature';
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function sign(string $payload, string $secret): string
    {
        $this->payload = $payload;
        $this->secret = $secret;

        return $this->signature;
    }
}
