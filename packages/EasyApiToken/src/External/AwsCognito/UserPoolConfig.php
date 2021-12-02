<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\AwsCognito;

use EonX\EasyApiToken\External\AwsCognito\Interfaces\UserPoolConfigInterface;

final class UserPoolConfig implements UserPoolConfigInterface
{
    /**
     * @var string
     */
    private $appClientId;

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $userPoolId;

    public function __construct(string $appClientId, string $region, string $userPoolId)
    {
        $this->appClientId = $appClientId;
        $this->region = $region;
        $this->userPoolId = $userPoolId;
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
