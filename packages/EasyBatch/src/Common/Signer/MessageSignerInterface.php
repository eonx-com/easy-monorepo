<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Signer;

interface MessageSignerInterface
{
    public function sign(string $payload): string;

    public function verify(string $payload, string $signature): bool;
}
