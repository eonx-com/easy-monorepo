<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Traits;

use EonX\EasyApiToken\Factories\Decoders\ApiKeyAsBasicAuthUsernameDecoderFactory;
use EonX\EasyApiToken\Factories\Decoders\BasicAuthDecoderFactory;
use EonX\EasyApiToken\Factories\Decoders\ChainReturnFirstTokenDecoderFactory;
use EonX\EasyApiToken\Factories\Decoders\JwtTokenDecoderFactory;
use EonX\EasyApiToken\Factories\Decoders\JwtTokenInQueryDecoderFactory;

trait DefaultDecoderFactoriesTrait
{
    /**
     * Get default decoder factories.
     *
     * @return string[]
     */
    private function getDefaultDecoderFactories(): array
    {
        return [
            'basic' => BasicAuthDecoderFactory::class,
            'chain' => ChainReturnFirstTokenDecoderFactory::class,
            'jwt-header' => JwtTokenDecoderFactory::class,
            'jwt-param' => JwtTokenInQueryDecoderFactory::class,
            'user-apikey' => ApiKeyAsBasicAuthUsernameDecoderFactory::class
        ];
    }
}
