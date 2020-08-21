<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use EonX\EasyApiToken\Decoders\ChainDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\DecoderNameAwareInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderSubFactoryInterface as SubFactory;
use EonX\EasyApiToken\Interfaces\Factories\MasterDecoderFactoryAwareInterface as MasterAware;
use EonX\EasyApiToken\Traits\DecoderNameAwareTrait;
use EonX\EasyApiToken\Traits\MasterDecoderFactoryAwareTrait;

/**
 * @deprecated since 2.4. Will be removed in 3.0. Use ApiTokenDecoderProvider instead.
 */
final class ChainReturnFirstTokenDecoderFactory implements SubFactory, MasterAware, DecoderNameAwareInterface
{
    use DecoderNameAwareTrait;
    use MasterDecoderFactoryAwareTrait;

    /**
     * @param null|mixed[] $config
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null, ?string $name = null): ApiTokenDecoderInterface
    {
        if ($config === null || empty($config['list'] ?? []) || \is_array($config['list']) === false) {
            throw new InvalidConfigurationException(\sprintf(
                '%s: "list" is required and must be an array for decoder "%s".',
                self::class,
                $this->decoderName
            ));
        }

        $decoders = [];
        foreach ($config['list'] as $decoder) {
            $decoders[] = $this->factory->build($decoder);
        }

        return new ChainDecoder($decoders, $name);
    }
}
