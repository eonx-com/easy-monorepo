<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Traits;

use LoyaltyCorp\EasyApiToken\Factories\Decoders\ApiKeyAsBasicAuthUsernameDecoderFactory;
use LoyaltyCorp\EasyApiToken\Factories\Decoders\BasicAuthDecoderFactory;
use LoyaltyCorp\EasyApiToken\Factories\Decoders\ChainReturnFirstTokenDecoderFactory;
use LoyaltyCorp\EasyApiToken\Factories\Decoders\JwtTokenDecoderFactory;
use LoyaltyCorp\EasyApiToken\Factories\Decoders\JwtTokenInQueryDecoderFactory;

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
