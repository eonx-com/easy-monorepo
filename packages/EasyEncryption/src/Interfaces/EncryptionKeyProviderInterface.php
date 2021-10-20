<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

interface EncryptionKeyProviderInterface
{
    /**
     * @param string $name
     *
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function getKey(string $keyName);

    public function hasKey(string $keyName): bool;
}
