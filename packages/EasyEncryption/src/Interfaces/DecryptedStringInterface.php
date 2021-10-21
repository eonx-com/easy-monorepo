<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

interface DecryptedStringInterface
{
    public function __toString(): string;

    public function getKeyName(): string;

    public function getRawDecryptedString(): string;
}
