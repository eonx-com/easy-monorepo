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

    /**
     * @return string[]
     */
    public function getSupportedKeyNames(): array
    {
        return \array_keys($this->config);
    }

    /**
     * @return string|mixed[]|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair
     */
    public function resolveKey(string $keyName)
    {
        return $this->config[$keyName];
    }
}
