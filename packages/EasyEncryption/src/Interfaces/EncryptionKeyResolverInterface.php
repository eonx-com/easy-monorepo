<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface EncryptionKeyResolverInterface extends HasPriorityInterface
{
    /**
     * @return string|mixed[]|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function resolveKey(string $keyName);

    public function supportsKey(string $keyName): bool;
}
