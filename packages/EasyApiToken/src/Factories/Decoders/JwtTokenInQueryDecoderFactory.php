<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Factories\Decoders;

use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory;

final class JwtTokenInQueryDecoderFactory extends AbstractJwtTokenDecoderFactory
{
    /**
     * Do build decoder factory for children classes.
     *
     * @param \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     * @param mixed[] $config
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    protected function doBuild(JwtDriverInterface $jwtDriver, array $config): EasyApiTokenDecoderInterface
    {
        $param = $config['options']['param'] ?? '';

        if (empty($param) || \is_string($param) === false) {
            throw new InvalidConfigurationException(\sprintf(
                '"param" is required and must be an string for decoder "%s".',
                $this->decoderName
            ));
        }

        return new JwtTokenInQueryDecoder(new JwtEasyApiTokenFactory($jwtDriver), $param);
    }
}
