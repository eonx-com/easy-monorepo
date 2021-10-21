<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\ValueObjects;

use EonX\EasyEncryption\Interfaces\DecryptedStringInterface;

final class DecryptedString implements DecryptedStringInterface
{
    /**
     * @var string
     */
    private $decryptedString;

    /**
     * @var string
     */
    private $keyName;

    public function __construct(string $decryptedString, string $keyName)
    {
        $this->decryptedString = $decryptedString;
        $this->keyName = $keyName;
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
