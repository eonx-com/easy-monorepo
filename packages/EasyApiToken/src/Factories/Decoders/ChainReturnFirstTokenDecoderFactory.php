<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use EonX\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\DecoderNameAwareInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderSubFactoryInterface as SubFactory;
use EonX\EasyApiToken\Interfaces\Factories\MasterDecoderFactoryAwareInterface as MasterAware;
use EonX\EasyApiToken\Traits\DecoderNameAwareTrait;
use EonX\EasyApiToken\Traits\MasterDecoderFactoryAwareTrait;

final class ChainReturnFirstTokenDecoderFactory implements SubFactory, MasterAware, DecoderNameAwareInterface
{
    use DecoderNameAwareTrait;
    use MasterDecoderFactoryAwareTrait;

    /**
     * Build api token decoder for given config.
     *
     * @param null|mixed[] $config
     *
     * @return \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function build(?array $config = null): EasyApiTokenDecoderInterface
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

        return new ChainReturnFirstTokenDecoder($decoders);
    }
}
