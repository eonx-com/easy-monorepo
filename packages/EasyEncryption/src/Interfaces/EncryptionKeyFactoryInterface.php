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
    public const OPTION_PUBLIC_KEY = 'public_key';

    /**
     * @var string
     */
    public const OPTION_SALT = 'salt';

    /**
     * @var string
     */
    public const OPTION_SECRET_KEY = 'secret_key';

    /**
     * @param mixed $key
     *
     * @return \ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function create($key);
}
