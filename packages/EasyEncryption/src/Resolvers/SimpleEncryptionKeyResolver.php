<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Resolvers;

use EonX\EasyEncryption\Exceptions\CouldNotResolveEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Utils\KeyLength;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

final class SimpleEncryptionKeyResolver extends AbstractEncryptionKeyResolver
{
    public function __construct(
        private string $keyName,
        private string $encryptionKey,
        private ?string $salt = null,
    ) {
    }

    public function supportsKey(string $keyName): bool
    {
        return $this->keyName === $keyName;
    }

    protected function doResolveKey(string $keyName): string|array|EncryptionKey|EncryptionKeyPair
    {
        // Key itself is enough
        if (KeyLength::isEncryptionKeyLength($this->encryptionKey)) {
            return $this->encryptionKey;
        }

        $salt = $this->salt ?? $this->encryptionKey;

        // Key itself wasn't enough, derive from salt or key itself if not salt provided
        if (KeyLength::isSaltLength($salt)) {
            return [
                EncryptionKeyFactoryInterface::OPTION_KEY => $this->encryptionKey,
                EncryptionKeyFactoryInterface::OPTION_SALT => $salt,
            ];
        }

        throw new CouldNotResolveEncryptionKeyException(\sprintf(
            'Given key must be either %d or %d bytes. Any other length requires a salt to be given',
            KeyLength::getSaltLength(),
            KeyLength::getEncryptionKeyLength()
        ));
    }
}
