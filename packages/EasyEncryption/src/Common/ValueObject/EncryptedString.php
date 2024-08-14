<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Common\ValueObject;

final class EncryptedString
{
    public function __construct(
        public string $encryptionKeyName,
        public string $value,
    ) {
    }
}
