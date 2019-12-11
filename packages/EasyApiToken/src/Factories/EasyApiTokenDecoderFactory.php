<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Factories;

use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\DecoderNameAwareInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderSubFactoryInterface;
use EonX\EasyApiToken\Interfaces\Factories\MasterDecoderFactoryAwareInterface;
use EonX\EasyApiToken\Traits\DefaultDecoderFactoriesTrait;
use Psr\Container\ContainerInterface;

class EasyApiTokenDecoderFactory implements EasyApiTokenDecoderFactoryInterface
{
    use DefaultDecoderFactoriesTrait;

    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $defaultFactories;

    /**
     * @var mixed[]
     */
    private $resolved = [];

    /**
     * EasyApiTokenDecoderFactory constructor.
     *
     * @param mixed[] $config
     * @param null|string[] $defaultFactories
     */
    public function __construct(array $config, ?array $defaultFactories = null)
    {
        $this->config = $config;
        $this->defaultFactories = $defaultFactories ?? $this->getDefaultDecoderFactories();
    }

    /**
     * Build a named TokenFactory.
     *
     * @param string $decoder Key of configuration found in the configuration.
     *
     * @return \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(string $decoder): EasyApiTokenDecoderInterface
    {
        if (isset($this->resolved[$decoder])) {
            return $this->resolved[$decoder];
        }

        if (empty($this->config) || \array_key_exists($decoder, $this->config) === false) {
            throw new InvalidConfigurationException(\sprintf('No decoder configured for key: "%s".', $decoder));
        }

        $config = $this->config[$decoder] ?? [];
        $subFactory = $this->instantiateSubFactory($decoder, $config);

        if ($subFactory instanceof DecoderNameAwareInterface) {
            $subFactory->setDecoderName($decoder);
        }
        if ($subFactory instanceof MasterDecoderFactoryAwareInterface) {
            $subFactory->setMasterFactory($this);
        }

        return $this->resolved[$decoder] = $subFactory->build($config);
    }

    /**
     * Set PSR container.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Get sub-factory class.
     *
     * @param string $decoder
     * @param mixed[] $config
     *
     * @return string
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    private function getSubFactoryClass(string $decoder, array $config): string
    {
        // If explicit type is set, use it
        if (empty($config['type']) === false && \is_string($config['type'])) {
            $type = $config['type'];

            // Allow type to be alias of default factory
            return empty($this->defaultFactories[$type]) === false ? $this->defaultFactories[$type] : $type;
        }

        // Default to factory for decoder
        if (empty($this->defaultFactories[$decoder]) === false) {
            return $this->defaultFactories[$decoder];
        }

        throw new InvalidConfigurationException(\sprintf(
            'No "type" or default factory configured for decoder "%s".',
            $decoder
        ));
    }

    /**
     * Instantiate sub-factory.
     *
     * @param string $decoder
     * @param mixed[] $config
     *
     * @return \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderSubFactoryInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    private function instantiateSubFactory(string $decoder, array $config): EasyApiTokenDecoderSubFactoryInterface
    {
        $factoryClass = $this->getSubFactoryClass($decoder, $config);

        try {
            if ($this->container !== null && $this->container->has($factoryClass)) {
                return $this->container->get($factoryClass);
            }

            if (\class_exists($factoryClass)) {
                return new $factoryClass();
            }
        } catch (\Exception $exception) {
            throw new InvalidConfigurationException(\sprintf(
                'Unable to instantiate the factory "%s" for decoder "%s": %s',
                $factoryClass,
                $decoder,
                $exception->getMessage()
            ), $exception->getCode(), $exception);
        }

        throw new InvalidConfigurationException(\sprintf(
            'Unable to instantiate the factory "%s" for decoder "%s".',
            $factoryClass,
            $decoder
        ));
    }
}
