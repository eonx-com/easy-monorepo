<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Common\ValueObject;

final readonly class DecryptedString implements DecryptedStringInterface
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
