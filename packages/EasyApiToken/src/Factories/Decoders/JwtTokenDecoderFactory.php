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
     * Do build decoder factory for children classes.
     *
     * @param \EonX\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     * @param mixed[] $config
     *
     * @return \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     */
    protected function doBuild(JwtDriverInterface $jwtDriver, array $config): EasyApiTokenDecoderInterface
    {
        return new JwtTokenDecoder(new JwtEasyApiTokenFactory($jwtDriver));
    }
}
