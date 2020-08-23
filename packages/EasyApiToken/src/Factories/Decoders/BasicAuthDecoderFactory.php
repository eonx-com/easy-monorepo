<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderSubFactoryInterface;

/**
 * @deprecated since 2.4. Will be removed in 3.0. Use ApiTokenDecoderProvider instead.
 */
final class BasicAuthDecoderFactory implements ApiTokenDecoderSubFactoryInterface
{
    /**
     * @param null|mixed[] $config
     */
    public function build(?array $config = null, ?string $name = null): ApiTokenDecoderInterface
    {
        return new BasicAuthDecoder($name);
    }
}
