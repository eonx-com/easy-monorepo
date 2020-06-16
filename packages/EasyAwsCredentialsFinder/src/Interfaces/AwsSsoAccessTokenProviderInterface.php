<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces;

interface AwsSsoAccessTokenProviderInterface
{
    public function getSsoAccessToken(): AwsSsoAccessTokenInterface;
}
