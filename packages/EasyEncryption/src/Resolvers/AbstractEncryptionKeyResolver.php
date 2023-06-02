<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Resolvers;

use EonX\EasyEncryption\Exceptions\CouldNotResolveEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractEncryptionKeyResolver implements EncryptionKeyResolverInterface
{
    use HasPriorityTrait;

    /**
     * @return string|mixed[]|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function resolveKey(string $keyName)
    {
        if ($this->supportsKey($keyName) === false) {
            throw new CouldNotResolveEncryptionKeyException(\sprintf(
                'Given key name "%s" not supported by %s',
                $keyName,
                static::class,
            ));
        }

        return $this->doResolveKey($keyName);
    }

    /**
     * @return string|mixed[]|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    abstract protected function doResolveKey(string $keyName);
}
