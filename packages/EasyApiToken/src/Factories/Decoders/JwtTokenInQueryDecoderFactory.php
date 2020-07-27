<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Factories\Decoders;

use EonX\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Tokens\Factories\JwtFactory;

final class JwtTokenInQueryDecoderFactory extends AbstractJwtTokenDecoderFactory
{
    /**
     * @param mixed[] $config
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    protected function doBuild(JwtDriverInterface $jwtDriver, array $config): ApiTokenDecoderInterface
    {
        $param = $config['options']['param'] ?? '';

        if (empty($param) || \is_string($param) === false) {
            throw new InvalidConfigurationException(\sprintf(
                '"param" is required and must be an string for decoder "%s".',
                $this->decoderName
            ));
        }

        return new JwtTokenInQueryDecoder(new JwtFactory($jwtDriver), $param);
    }
}
