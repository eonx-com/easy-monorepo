<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use EonX\EasyApiToken\Decoders\JwtTokenDecoder;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Tokens\Factories\JwtFactory;

final class JwtTokenDecoderFactory extends AbstractJwtTokenDecoderFactory
{
    /**
     * @param mixed[] $config
     */
    protected function doBuild(
        JwtDriverInterface $jwtDriver,
        array $config,
        ?string $name = null
    ): ApiTokenDecoderInterface {
        return new JwtTokenDecoder(new JwtFactory($jwtDriver), null, $name);
    }
}
