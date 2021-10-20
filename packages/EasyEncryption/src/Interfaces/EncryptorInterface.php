<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

interface EncryptorInterface
{
    /**
     * @var string
     */
    public const DEFAULT_KEY_NAME = 'app';

    /**
     * @param null|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     */
    public function encrypt(string $text, $key = null): string;

    /**
     * @param null|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair $key
     */
    public function decrypt(string $text, $key = null): string;
}
