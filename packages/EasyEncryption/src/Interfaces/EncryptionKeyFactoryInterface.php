<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

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

    public function create(mixed $key): EncryptionKey|EncryptionKeyPair;
}
