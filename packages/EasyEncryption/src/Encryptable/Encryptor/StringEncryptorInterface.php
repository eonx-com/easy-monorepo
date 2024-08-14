<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Encryptor;

use EonX\EasyEncryption\Encryptable\ValueObject\EncryptedString;

interface StringEncryptorInterface
{
    public function decrypt(string $text): string;

    public function encrypt(string $text): EncryptedString;
}
