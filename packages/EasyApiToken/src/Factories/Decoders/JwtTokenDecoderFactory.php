<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use EonX\EasyApiToken\Decoders\JwtTokenDecoder;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory;

final class JwtTokenDecoderFactory extends AbstractJwtTokenDecoderFactory
{
    /**
     * @param mixed[] $config
     */
    protected function doBuild(JwtDriverInterface $jwtDriver, array $config): EasyApiTokenDecoderInterface
    {
        return new JwtTokenDecoder(new JwtEasyApiTokenFactory($jwtDriver));
    }
}
