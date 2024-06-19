<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\AwsCognito\ValueObject;

interface UserPoolConfigInterface
{
    public const ISSUING_URL_PATTERN = 'https://cognito-idp.%s.amazonaws.com/%s';

    public const JWKS_URL_PATTERN = 'https://cognito-idp.%s.amazonaws.com/%s/.well-known/jwks.json';

    public function getAppClientId(): string;

    public function getIssuingUrl(): string;

    public function getJwksUrl(): string;

    public function getRegion(): string;

    public function getUserPoolId(): string;
}
