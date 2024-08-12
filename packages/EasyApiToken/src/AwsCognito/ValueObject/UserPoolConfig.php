<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\AwsCognito\ValueObject;

final readonly class UserPoolConfig implements UserPoolConfigInterface
{
    private const ISSUING_URL_PATTERN = 'https://cognito-idp.%s.amazonaws.com/%s';

    private const JWKS_URL_PATTERN = 'https://cognito-idp.%s.amazonaws.com/%s/.well-known/jwks.json';

    public function __construct(
        private string $appClientId,
        private string $region,
        private string $userPoolId,
    ) {
    }

    public function getAppClientId(): string
    {
        return $this->appClientId;
    }

    public function getIssuingUrl(): string
    {
        return \sprintf(self::ISSUING_URL_PATTERN, $this->region, $this->userPoolId);
    }

    public function getJwksUrl(): string
    {
        return \sprintf(self::JWKS_URL_PATTERN, $this->region, $this->userPoolId);
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getUserPoolId(): string
    {
        return $this->userPoolId;
    }
}
