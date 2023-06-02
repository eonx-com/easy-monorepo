<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Resolvers;

use EonX\EasyEncryption\Exceptions\CouldNotResolveEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Utils\KeyLength;

final class SimpleEncryptionKeyResolver extends AbstractEncryptionKeyResolver
{
    /**
     * @var string
     */
    private $encryptionKey;

    /**
     * @var string
     */
    private $keyName;

    /**
     * @var null|string
     */
    private $salt;

    public function __construct(string $keyName, string $encryptionKey, ?string $salt = null)
    {
        $this->keyName = $keyName;
        $this->encryptionKey = $encryptionKey;
        $this->salt = $salt;
    }

    public function supportsKey(string $keyName): bool
    {
        return $this->keyName === $keyName;
    }

    protected function doResolveKey(string $keyName)
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
