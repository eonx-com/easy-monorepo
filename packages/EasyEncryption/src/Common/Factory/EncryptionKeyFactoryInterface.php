<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Common\Factory;

use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

interface EncryptionKeyFactoryInterface
{
    public function create(mixed $key): EncryptionKey|EncryptionKeyPair;
}
