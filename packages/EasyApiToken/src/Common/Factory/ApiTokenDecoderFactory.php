<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Factory;

use EonX\EasyApiToken\Common\Decoder\ApiKeyDecoder;
use EonX\EasyApiToken\Common\Decoder\DecoderInterface;
use EonX\EasyApiToken\Common\Driver\HashedApiKeyDriverInterface;
use EonX\EasyApiToken\Common\Exception\InvalidConfigurationException;
use EonX\EasyApiToken\Common\Exception\InvalidDefaultDecoderException;
use EonX\EasyApiToken\Common\Provider\DecoderProviderInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;

final class ApiTokenDecoderFactory implements ApiTokenDecoderFactoryInterface
{
    /**
     * @var \EonX\EasyApiToken\Common\Provider\DecoderProviderInterface[]
     */
    private array $decoderProviders;

    /**
     * @var \EonX\EasyApiToken\Common\Decoder\DecoderInterface[]|null
     */
    private ?array $decoders = null;

    private ?string $defaultDecoder = null;

    public function __construct(
        iterable $decoderProviders,
        private readonly HashedApiKeyDriverInterface $hashedApiKeyDriver,
    ) {
        $this->decoderProviders = $this->filter($decoderProviders, DecoderProviderInterface::class);
    }

    /**
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidDefaultDecoderException
     */
    public function build(?string $decoder = null): DecoderInterface
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
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidDefaultDecoderException
     */
    public function buildDefault(): DecoderInterface
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
            foreach ($this->filter($provider->getDecoders(), DecoderInterface::class) as $decoder) {
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
