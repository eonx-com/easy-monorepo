<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Resolver;

use EonX\EasyEncryption\Common\Resolver\AbstractEncryptionKeyResolver;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

final class EncryptionKeyResolverStub extends AbstractEncryptionKeyResolver
{
    private array $config;

    public function __construct(array|callable $config)
    {
        $this->config = \is_array($config) ? $config : $config();
    }

    public function supportsKey(string $keyName): bool
    {
        return isset($this->config[$keyName]);
    }

    protected function doResolveKey(string $keyName): string|array|EncryptionKey|EncryptionKeyPair
    {
        return $this->config[$keyName];
    }
}
