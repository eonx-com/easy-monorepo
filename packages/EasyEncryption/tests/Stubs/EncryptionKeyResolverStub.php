<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stubs;

use EonX\EasyEncryption\Resolvers\AbstractEncryptionKeyResolver;

final class EncryptionKeyResolverStub extends AbstractEncryptionKeyResolver
{
    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @param mixed[]|callable $config
     */
    public function __construct($config)
    {
        $this->config = \is_array($config) ? $config : \call_user_func($config);
    }

    public function supportsKey(string $keyName): bool
    {
        return isset($this->config[$keyName]);
    }

    /**
     * @return string|mixed[]|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    protected function doResolveKey(string $keyName)
    {
        return $this->config[$keyName];
    }
}
