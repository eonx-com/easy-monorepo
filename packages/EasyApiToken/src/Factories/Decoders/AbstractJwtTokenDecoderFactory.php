<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use Auth0\SDK\Helpers\Cache\FileSystemCacheHandler;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\External\Auth0JwtDriver;
use EonX\EasyApiToken\External\FirebaseJwtDriver;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\DecoderNameAwareInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderSubFactoryInterface as DecoderSubFactory;
use EonX\EasyApiToken\Traits\DecoderNameAwareTrait;

abstract class AbstractJwtTokenDecoderFactory implements DecoderSubFactory, DecoderNameAwareInterface
{
    use DecoderNameAwareTrait;

    /**
     * @var string[]
     */
    protected static $jwtDrivers = ['auth0', 'firebase'];

    /**
     * @param null|mixed[] $config
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null): ApiTokenDecoderInterface
    {
        $config = $config ?? [];

        if (empty($config['driver'] ?? '') || \is_string($config['driver']) === false) {
            throw new InvalidConfigurationException(\sprintf(
                '"driver" is required and must be a string for decoder "%s".',
                $this->decoderName
            ));
        }

        if (empty($config['options'] ?? []) || \is_array($config['options']) === false) {
            throw new InvalidConfigurationException(\sprintf(
                '"options" is required and must be an array for decoder "%s".',
                $this->decoderName
            ));
        }

        return $this->doBuild($this->createJwtDriver($config['driver'], $config['options']), $config);
    }

    /**
     * @param mixed[] $config
     */
    abstract protected function doBuild(JwtDriverInterface $jwtDriver, array $config): ApiTokenDecoderInterface;

    /**
     * @param mixed[] $options List of options to use to create Driver.
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    protected function createJwtDriver(string $driver, array $options): JwtDriverInterface
    {
        switch ($driver) {
            case 'auth0':
                return $this->createAuth0Driver($options);
            case 'firebase':
                return $this->createFirebaseDriver($options);
        }

        throw new InvalidConfigurationException(\sprintf(
            '"driver" value "%s" is invalid. Valid drivers: ["%s"].',
            $driver,
            \implode('", "', static::$jwtDrivers)
        ));
    }

    /**
     * @param mixed[] $options List of options to pass to Auth0JwtDriver. Keys match constructor parameters.
     */
    private function createAuth0Driver(array $options): Auth0JwtDriver
    {
        $cache = empty($options['cache_path']) === false ? new FileSystemCacheHandler($options['cache_path']) : null;

        return new Auth0JwtDriver(
            $options['valid_audiences'],
            $options['authorized_iss'],
            $options['private_key'] ?? null, // Required only for HS256
            $options['audience_for_encode'] ?? null,
            $options['allowed_algos'] ?? null,
            $cache
        );
    }

    /**
     * @param mixed[] $options List of options to pass to FirebaseJwtDriver. Keys match constructor parameters.
     */
    private function createFirebaseDriver(array $options): FirebaseJwtDriver
    {
        return new FirebaseJwtDriver(
            $options['algo'],
            $options['public_key'],
            $options['private_key'],
            $options['allowed_algos'] ?? null,
            $options['leeway'] ?? null
        );
    }
}
