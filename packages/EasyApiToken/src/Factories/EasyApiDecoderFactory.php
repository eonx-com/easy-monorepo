<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Factories;

use LoyaltyCorp\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\BasicAuthDecoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;

class EasyApiDecoderFactory
{
    /**
     * @var array
     */
    private $config;

    /**
     * EasyApiDecoderFactory constructor.
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
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(string $configKey): EasyApiTokenDecoderInterface
    {
        if (\count($this->config) === 0) {
            throw new InvalidConfigurationException('Could not find a valid configuration.');
        }
        if (\array_key_exists($configKey, $this->config) === false) {
            throw new InvalidConfigurationException(
                \sprintf('Could not find EasyApiConfiguration for key: %s.', $configKey)
            );
        }

        $driver = $this->config[$configKey]['driver'] ?? '';

        switch ($driver) {
            case 'basic':
                return new BasicAuthDecoder();
            case 'user-apikey':
                return new ApiKeyAsBasicAuthUsernameDecoder();
        }
        throw new InvalidConfigurationException(
            \sprintf('Invalid EasyApiToken driver: %s configured for key: %s.', $driver,$configKey)
        );
    }
}