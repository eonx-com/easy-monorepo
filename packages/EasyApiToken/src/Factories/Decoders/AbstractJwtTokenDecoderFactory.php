<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use Auth0\SDK\Helpers\Cache\FileSystemCacheHandler;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\External\Auth0JwtDriver;
use EonX\EasyApiToken\External\FirebaseJwtDriver;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\DecoderNameAwareInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderSubFactoryInterface as DecoderSubFactory;
use EonX\EasyApiToken\Traits\DecoderNameAwareTrait;

abstract class AbstractJwtTokenDecoderFactory implements DecoderSubFactory, DecoderNameAwareInterface
{
    use DecoderNameAwareTrait;

    /**
     * @var string[]
     */
    protected static $jwtDrivers = ['auth0', 'firebase'];

    /**
     * Build api token decoder for given config.
     *
     * @param null|mixed[] $config
     *
     * @return \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null): EasyApiTokenDecoderInterface
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
     * Do build decoder factory for children classes.
     *
     * @param \EonX\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     * @param mixed[] $config
     *
     * @return \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     */
    abstract protected function doBuild(JwtDriverInterface $jwtDriver, array $config): EasyApiTokenDecoderInterface;

    /**
     * Build a JWT driver.
     *
     * @param string $driver Driver to build, must be one of 'auth0' or 'firebase'.
     * @param mixed[] $options List of options to use to create Driver.
     *
     * @return \EonX\EasyApiToken\External\Interfaces\JwtDriverInterface
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
     * Build a Auth09 JWT Driver.
     *
     * @param mixed[] $options List of options to pass to Auth0JwtDriver. Keys match constructor parameters.
     *
     * @return \EonX\EasyApiToken\External\Auth0JwtDriver
     */
    private function createAuth0Driver(array $options): Auth0JwtDriver
    {
        $cache = empty($options['cache_path']) === false ? new FileSystemCacheHandler($options['cache_path']) : null;

        $driver = new Auth0JwtDriver(
            $options['valid_audiences'],
            $options['authorized_iss'],
            $options['private_key'],
            $options['audience_for_encode'] ?? null,
            $options['allowed_algos'] ?? null,
            $cache
        );

        return $driver;
    }

    /**
     * Build a Firebase JWT Driver.
     *
     * @param mixed[] $options List of options to pass to FirebaseJwtDriver. Keys match constructor parameters.
     *
     * @return \EonX\EasyApiToken\External\FirebaseJwtDriver
     */
    private function createFirebaseDriver(array $options): FirebaseJwtDriver
    {
        $driver = new FirebaseJwtDriver(
            $options['algo'],
            $options['public_key'],
            $options['private_key'],
            $options['allowed_algos'] ?? null,
            $options['leeway'] ?? null
        );

        return $driver;
    }
}
