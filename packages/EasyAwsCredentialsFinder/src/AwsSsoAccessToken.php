<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder;

use EonX\EasyAwsCredentialsFinder\Interfaces\AwsSsoAccessTokenInterface;

final class AwsSsoAccessToken implements AwsSsoAccessTokenInterface
{
    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var \DateTimeInterface
     */
    private $expiration;

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $startUrl;

    public function __construct(string $accessToken, \DateTimeInterface $expiration, string $region, string $startUrl)
    {
        $this->accessToken = $accessToken;
        $this->expiration = $expiration;
        $this->region = $region;
        $this->startUrl = $startUrl;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getExpiration(): \DateTimeInterface
    {
        return $this->expiration;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getStartUrl(): string
    {
        return $this->startUrl;
    }
}
