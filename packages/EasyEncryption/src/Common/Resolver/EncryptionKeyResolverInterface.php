<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Common\Resolver;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

interface EncryptionKeyResolverInterface extends HasPriorityInterface
{
    public function resolveKey(string $keyName): string|array|EncryptionKey|EncryptionKeyPair;

    public function supportsKey(string $keyName): bool;
}
