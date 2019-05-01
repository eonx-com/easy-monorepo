<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Factories;

use LoyaltyCorp\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\BasicAuthDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\External\Auth0JwtDriver;
use LoyaltyCorp\EasyApiToken\External\FirebaseJwtDriver;
use LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory;

/**
 * Build an EasyApiDecoder from a configuration file.
 */
class EasyApiTokenDecoderFactory
{
    /**
     * @var array
     */
    private $config;

    /**
     * EasyApiTokenDecoderFactory constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Build a named TokenFactory.
     *
     * @param string $configKey Key of configuration found in the configuration.
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(string $configKey): EasyApiTokenDecoderInterface
    {
        if (\count($this->config) === 0) {
            throw new InvalidConfigurationException('Could not find a valid configuration.');
        }
        if (\array_key_exists($configKey, $this->config) === false) {
            throw new InvalidConfigurationException(
                \sprintf('Could not find EasyApiToken for key: %s.', $configKey)
            );
        }

        $decoderType = $this->config[$configKey]['type'] ?? '';

        switch ($decoderType) {
            case 'basic':
                return new BasicAuthDecoder();
            case 'user-apikey':
                return new ApiKeyAsBasicAuthUsernameDecoder();
            case 'chain':
                return $this->createChain($configKey, $this->config[$configKey]);
        }

        if (\array_key_exists('options', $this->config[$configKey]) === false) {
            throw new InvalidConfigurationException(\sprintf(
                'Missing options array for EasyApiToken decoder: %s.', $configKey
            ));
        }
        if (\array_key_exists('driver', $this->config[$configKey]) === false) {
            throw new InvalidConfigurationException(\sprintf(
                'EasyApiToken decoder: %s is missing a driver key.', $configKey
            ));
        }

        switch ($decoderType) {
            case 'jwt-header':
                return $this->createJwtHeaderDecoder($this->config[$configKey]);
            case 'jwt-param':
                return $this->createJwtParamDecoder($this->config[$configKey]);
        }
        throw new InvalidConfigurationException(
            \sprintf('Invalid EasyApiToken decoder type: %s configured for key: %s.', $decoderType, $configKey)
        );
    }

    /**
     * Build a chain Decoder.
     *
     * @param string $name The name of this chain driver being requested.
     * @param array $config Configuration options for the chain driver. Should have a single item named 'list'.
     *
     * @return \LoyaltyCorp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    private function createChain($name, $config): ChainReturnFirstTokenDecoder
    {
        if (\array_key_exists('list', $config) === false) {
            throw new InvalidConfigurationException(\sprintf(
                'EasyApiToken decoder: %s is missing a required list option.', $name
            ));
        }
        $driverKeys = $config['list'];

        $list = [];
        foreach ($driverKeys as $key) {
            $list[] = $this->build($key);
        }
        return new ChainReturnFirstTokenDecoder($list);
    }

    /**
     * Build a JWT parameter decoder.
     *
     * @param array $configuration Options for building decoder.
     *
     * @return \LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    private function createJwtHeaderDecoder(array $configuration): JwtTokenDecoder
    {
        $driverName = $configuration['driver'];
        $options = $configuration['options'];

        $driver = $this->createJwtDriver($driverName, $options);
        return new JwtTokenDecoder(new JwtEasyApiTokenFactory($driver));
    }

    /**
     * Build a JWT request decoder.
     *
     * @param array $configuration Options for building decoder.
     *
     * @return \LoyaltyCorp\EasyApiToken\Decoders\JwtTokenInQueryDecoder
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    private function createJwtParamDecoder(array $configuration): JwtTokenInQueryDecoder
    {
        $driverName = $configuration['driver'];
        $options = $configuration['options'];

        $driver = $this->createJwtDriver($driverName, $options);
        return new JwtTokenInQueryDecoder(new JwtEasyApiTokenFactory($driver), $options['param']);
    }

    /**
     * Build a JWT driver.
     *
     * @param string $driver Driver to build, must be one of 'auth0' or 'firebase'.
     * @param array $options List of options to use to create Driver.
     *
     * @return \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    private function createJwtDriver($driver, $options): JwtDriverInterface
    {
        switch ($driver) {
            case 'auth0':
                return $this->createAuth0Driver($options);
            case 'firebase':
                return $this->createFirebaseDriver($options);
        }
        throw new InvalidConfigurationException(\sprintf(
            'Invalid JWT decoder driver: %s.', $driver
        ));
    }

    /**
     * Build a Auth09 JWT Driver.
     *
     * @param mixed[] $options List of options to pass to Auth0JwtDriver. Keys match constructor parameters.
     *
     * @return \LoyaltyCorp\EasyApiToken\External\Auth0JwtDriver
     */
    private function createAuth0Driver($options): Auth0JwtDriver
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
    private function createFirebaseDriver($options): FirebaseJwtDriver
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
