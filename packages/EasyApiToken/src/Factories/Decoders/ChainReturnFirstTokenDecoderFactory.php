<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Factories\Decoders;

use LoyaltyCorp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Factories\DecoderNameAwareInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderSubFactoryInterface as SubFactory;
use LoyaltyCorp\EasyApiToken\Interfaces\Factories\MasterDecoderFactoryAwareInterface as MasterAware;
use LoyaltyCorp\EasyApiToken\Traits\DecoderNameAwareTrait;
use LoyaltyCorp\EasyApiToken\Traits\MasterDecoderFactoryAwareTrait;

final class ChainReturnFirstTokenDecoderFactory implements SubFactory, MasterAware, DecoderNameAwareInterface
{
    use DecoderNameAwareTrait;
    use MasterDecoderFactoryAwareTrait;

    /**
     * Build api token decoder for given config.
     *
     * @param null|mixed[] $config
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
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
