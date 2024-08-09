<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptors;

use EonX\EasyEncryption\ValueObjects\EncryptedString;

interface StringEncryptorInterface
{
    public function decrypt(string $text): string;

    public function encrypt(string $text): EncryptedString;
}
