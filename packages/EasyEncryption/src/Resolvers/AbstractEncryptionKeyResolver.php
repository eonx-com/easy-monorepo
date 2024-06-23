<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Resolvers;

use EonX\EasyEncryption\Exceptions\CouldNotResolveEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

abstract class AbstractEncryptionKeyResolver implements EncryptionKeyResolverInterface
{
    use HasPriorityTrait;

    public function resolveKey(string $keyName): string|array|EncryptionKey|EncryptionKeyPair
    {
        if ($this->supportsKey($keyName) === false) {
            throw new CouldNotResolveEncryptionKeyException(\sprintf(
                'Given key name "%s" not supported by %s',
                $keyName,
                static::class
            ));
        }

        return $this->doResolveKey($keyName);
    }

    abstract protected function doResolveKey(string $keyName): string|array|EncryptionKey|EncryptionKeyPair;
}
