<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\ValueObject;

use EonX\EasyEncryption\Interfaces\DecryptedStringInterface;

final class DecryptedString implements DecryptedStringInterface
{
    public function __construct(
        private string $decryptedString,
        private string $keyName,
    ) {
    }

    public function __toString(): string
    {
        return $this->getRawDecryptedString();
    }

    public function getKeyName(): string
    {
        return $this->keyName;
    }

    public function getRawDecryptedString(): string
    {
        return $this->decryptedString;
    }
}
