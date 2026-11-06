<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Signer;

interface MessengerEnvelopeSignerInterface
{
    public function sign(string $payload): string;

    public function verify(string $payload, string $signature): bool;
}
