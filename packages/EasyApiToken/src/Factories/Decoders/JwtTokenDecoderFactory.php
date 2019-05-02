<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Factories\Decoders;

use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder;
use LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory;

final class JwtTokenDecoderFactory extends AbstractJwtTokenDecoderFactory
{
    /**
     * Do build decoder factory for children classes.
     *
     * @param \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     * @param mixed[] $config
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     */
    protected function doBuild(JwtDriverInterface $jwtDriver, array $config): EasyApiTokenDecoderInterface
    {
        return new JwtTokenDecoder(new JwtEasyApiTokenFactory($jwtDriver));
    }
}
