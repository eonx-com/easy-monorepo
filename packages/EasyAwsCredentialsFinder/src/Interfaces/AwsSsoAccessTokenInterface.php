<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces;

interface AwsSsoAccessTokenInterface
{
    public function getAccessToken(): string;

    public function getExpiration(): \DateTimeInterface;

    public function getRegion(): string;

    public function getStartUrl(): string;
}
