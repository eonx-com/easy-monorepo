<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Factories\Decoders;

use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\External\Auth0JwtDriver;
use LoyaltyCorp\EasyApiToken\External\FirebaseJwtDriver;
use LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Factories\DecoderNameAwareInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderSubFactoryInterface as DecoderSubFactory;
use LoyaltyCorp\EasyApiToken\Traits\DecoderNameAwareTrait;

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
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null): EasyApiTokenDecoderInterface
    {
        if ($config === null || empty($config['driver'] ?? '') || \is_string($config['driver']) === false) {
            throw new InvalidConfigurationException(\sprintf(
                '"driver" is required and must be a string for decoder "%s".',
                $this->decoderName
            ));
        }

        if ($config === null || empty($config['options'] ?? []) || \is_array($config['options']) === false) {
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
     * @param \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     * @param mixed[] $config
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     */
    abstract protected function doBuild(JwtDriverInterface $jwtDriver, array $config): EasyApiTokenDecoderInterface;

    /**
     * Build a JWT driver.
     *
     * @param string $driver Driver to build, must be one of 'auth0' or 'firebase'.
     * @param mixed[] $options List of options to use to create Driver.
     *
     * @return \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
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
     * @return \LoyaltyCorp\EasyApiToken\External\Auth0JwtDriver
     */
    private function createAuth0Driver(array $options): Auth0JwtDriver
    {
        $driver = new Auth0JwtDriver(
            $options['valid_audiences'],
            $options['authorized_iss'],
            $options['private_key'],
            $options['audience_for_encode'] ?? null,
            $options['allowed_algos'] ?? null
        );

        return $driver;
    }

    /**
     * Build a Firebase JWT Driver.
     *
     * @param mixed[] $options List of options to pass to FirebaseJwtDriver. Keys match constructor parameters.
     *
     * @return \LoyaltyCorp\EasyApiToken\External\FirebaseJwtDriver
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
