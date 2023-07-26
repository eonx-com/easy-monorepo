<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

interface EncryptionKeyFactoryInterface
{
    public const OPTION_KEY = 'key';

    public const OPTION_PUBLIC_KEY = 'public_key';

    public const OPTION_SALT = 'salt';

    public const OPTION_SECRET_KEY = 'secret_key';

    public function create(mixed $key): EncryptionKey|EncryptionKeyPair;
}
