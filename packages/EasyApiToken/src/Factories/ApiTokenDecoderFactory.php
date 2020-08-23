<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Factories;

use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Exceptions\InvalidDefaultDecoderException;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderProviderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;

class ApiTokenDecoderFactory implements ApiTokenDecoderFactoryInterface
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface[]
     */
    private $decoders;

    /**
     * @var string
     */
    private $defaultDecoder;

    /**
     * @param mixed[] $config
     * @param null|string[] $defaultFactories
     */
    public function __construct(iterable $decoderProviders)
    {
        $this->setDecoders($decoderProviders);
    }

    public function build(?string $decoder = null): ApiTokenDecoderInterface
    {
        if ($decoder === null) {
            return $this->buildDefault();
        }

        if (isset($this->decoders[$decoder])) {
            return $this->decoders[$decoder];
        }

        throw new InvalidConfigurationException(\sprintf('No decoder configured for key: "%s".', $decoder));
    }

    public function buildDefault(): ApiTokenDecoderInterface
    {
        if ($this->defaultDecoder !== null) {
            return $this->build($this->defaultDecoder);
        }

        throw new InvalidDefaultDecoderException('No default decoder set');
    }

    /**
     * @param iterable<mixed> $collection
     *
     * @return mixed[]
     */
    private function filter(iterable $collection, string $class): array
    {
        $collection = $collection instanceof \Traversable ? \iterator_to_array($collection) : (array)$collection;

        $collection = \array_filter($collection, static function ($item) use ($class): bool {
            return $item instanceof $class;
        });

        if ($class === ApiTokenDecoderProviderInterface::class) {
            \usort(
                $collection,
                static function (
                    ApiTokenDecoderProviderInterface $first,
                    ApiTokenDecoderProviderInterface $second
                ): int {
                    return $first->getPriority() <=> $second->getPriority();
                }
            );
        }

        return $collection;
    }

    /**
     * @param iterable<mixed> $providers
     */
    private function setDecoders(iterable $providers): void
    {
        $decoders = [];

        foreach ($this->filter($providers, ApiTokenDecoderProviderInterface::class) as $provider) {
            foreach ($this->filter($provider->getDecoders(), ApiTokenDecoderInterface::class) as $decoder) {
                $decoders[$decoder->getName()] = $decoder;
            }

            if ($provider->getDefaultDecoder() !== null) {
                $this->defaultDecoder = $provider->getDefaultDecoder();
            }
        }

        $this->decoders = $decoders;
    }
}

\class_alias(ApiTokenDecoderFactory::class, EasyApiTokenDecoderFactory::class);
