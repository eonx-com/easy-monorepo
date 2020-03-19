<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderSubFactoryInterface;

final class BasicAuthDecoderFactory implements EasyApiTokenDecoderSubFactoryInterface
{
    /**
     * @param null|mixed[] $config
     */
    public function build(?array $config = null): EasyApiTokenDecoderInterface
    {
        return new BasicAuthDecoder();
    }
}
