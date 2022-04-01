<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Factories;

use EonX\EasyApiToken\Decoders\ApiKeyDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Exceptions\InvalidDefaultDecoderException;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderProviderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyDriverInterface;
use EonX\EasyUtils\CollectorHelper;

final class ApiTokenDecoderFactory implements ApiTokenDecoderFactoryInterface
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface[]
     */
    private array $decoders;

    private ?string $defaultDecoder = null;

    /**
     * @param iterable<mixed> $decoderProviders
     */
    public function __construct(
        iterable $decoderProviders,
        private HashedApiKeyDriverInterface $hashedApiKeyDriver
    ) {
        $this->setDecoders($decoderProviders);
    }

    /**
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidDefaultDecoderException
     */
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

    /**
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidDefaultDecoderException
     */
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
        return CollectorHelper::orderLowerPriorityFirstAsArray(CollectorHelper::filterByClass($collection, $class));
    }

    /**
     * @param iterable<mixed> $providers
     */
    private function setDecoders(iterable $providers): void
    {
        $decoders = [];

        foreach ($this->filter($providers, ApiTokenDecoderProviderInterface::class) as $provider) {
            foreach ($this->filter($provider->getDecoders(), ApiTokenDecoderInterface::class) as $decoder) {
                if ($decoder instanceof ApiKeyDecoder) {
                    $decoder->setHashedApiKeyDriver($this->hashedApiKeyDriver);
                }

                $decoders[$decoder->getName()] = $decoder;
            }

            if ($provider->getDefaultDecoder() !== null) {
                $this->defaultDecoder = $provider->getDefaultDecoder();
            }
        }

        $this->decoders = $decoders;
    }
}
