<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\AwsCognito\Interfaces;

interface UserPoolConfigInterface
{
    /**
     * @var string
     */
    public const ISSUING_URL_PATTERN = 'https://cognito-idp.%s.amazonaws.com/%s';

    /**
     * @var string
     */
    public const JWKS_URL_PATTERN = 'https://cognito-idp.%s.amazonaws.com/%s/.well-known/jwks.json';

    public function getAppClientId(): string;

    public function getIssuingUrl(): string;

    public function getJwksUrl(): string;

    public function getRegion(): string;

    public function getUserPoolId(): string;
}
