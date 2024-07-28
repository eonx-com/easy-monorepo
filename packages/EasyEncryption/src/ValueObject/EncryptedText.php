<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\ValueObject;

final class EncryptedText
{
    public function __construct(
        public string $encryptionKeyName,
        public string $value,
    ) {
    }
}
