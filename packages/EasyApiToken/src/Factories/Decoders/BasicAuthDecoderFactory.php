<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Factories\Decoders;

use LoyaltyCorp\EasyApiToken\Decoders\BasicAuthDecoder;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderSubFactoryInterface;

final class BasicAuthDecoderFactory implements EasyApiTokenDecoderSubFactoryInterface
{
    /**
     * Build api token decoder for given config.
     *
     * @param null|mixed[] $config
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     */
    public function build(?array $config = null): EasyApiTokenDecoderInterface
    {
        return new BasicAuthDecoder();
    }
}
