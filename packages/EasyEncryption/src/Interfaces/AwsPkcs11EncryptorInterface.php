<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

interface AwsPkcs11EncryptorInterface extends EncryptorInterface
{
    public function reset(): void;

    public function sign(string $text, ?string $keyName = null): string;
}
