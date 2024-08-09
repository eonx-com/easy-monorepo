<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptors;

use EonX\EasyEncryption\Interfaces\EncryptableInterface;

interface ObjectEncryptorInterface
{
    public function decrypt(EncryptableInterface $encryptable): void;

    public function encrypt(EncryptableInterface $encryptable): void;
}
