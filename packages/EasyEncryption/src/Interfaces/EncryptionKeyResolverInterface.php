<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

interface EncryptionKeyResolverInterface extends HasPriorityInterface
{
    /**
     * @return string|mixed[]|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function resolveKey(string $keyName): string|array|EncryptionKey|EncryptionKeyPair;

    public function supportsKey(string $keyName): bool;
}
