<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\AwsCognito\ValueObject;

interface UserPoolConfigInterface
{
    public function getAppClientId(): string;

    public function getIssuingUrl(): string;

    public function getJwksUrl(): string;

    public function getRegion(): string;

    public function getUserPoolId(): string;
}
