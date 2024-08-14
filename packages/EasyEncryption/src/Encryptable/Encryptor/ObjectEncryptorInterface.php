<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Encryptor;

use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface;

interface ObjectEncryptorInterface
{
    public function decrypt(EncryptableInterface $encryptable): void;

    public function encrypt(EncryptableInterface $encryptable): void;
}
