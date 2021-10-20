<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

interface EncryptionKeyFactoryInterface
{
    /**
     * @var string
     */
    public const OPTION_KEY = 'key';

    /**
     * @var string
     */
    public const OPTION_SALT = 'salt';

    /**
     * @param mixed $key
     *
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function create($key);
}
