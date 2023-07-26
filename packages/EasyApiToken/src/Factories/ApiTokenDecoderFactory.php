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
use EonX\EasyUtils\Helpers\CollectorHelper;

final class ApiTokenDecoderFactory implements ApiTokenDecoderFactoryInterface
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\ApiTokenDecoderProviderInterface[]
     */
    private array $decoderProviders;

    /**
     * @var \EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface[]|null
     */
    private ?array $decoders = null;

    private ?string $defaultDecoder = null;

    /**
     * @param iterable<mixed> $decoderProviders
     */
    public function __construct(
        iterable $decoderProviders,
        private readonly HashedApiKeyDriverInterface $hashedApiKeyDriver,
    ) {
        $this->decoderProviders = $this->filter($decoderProviders, ApiTokenDecoderProviderInterface::class);
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

        $this->initDecoders();

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
        $this->initDecoders();

        if ($this->defaultDecoder !== null) {
            return $this->build($this->defaultDecoder);
        }

        throw new InvalidDefaultDecoderException('No default decoder set');
    }

    public function reset(): void
    {
        $this->decoders = null;

        foreach ($this->decoderProviders as $provider) {
            if (\method_exists($provider, 'reset')) {
                $provider->reset();
            }
        }
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

    private function initDecoders(): void
    {
        if ($this->decoders === null) {
            $this->setDecoders();
        }
    }

    private function setDecoders(): void
    {
        $decoders = [];

        foreach ($this->decoderProviders as $provider) {
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
