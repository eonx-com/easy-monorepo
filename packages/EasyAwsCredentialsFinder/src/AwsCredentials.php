<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder;

use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsInterface;

final class AwsCredentials implements AwsCredentialsInterface
{
    /**
     * @var null|string
     */
    private $accessKeyId;

    /**
     * @var null|\DateTimeInterface
     */
    private $expiration;

    /**
     * @var null|string
     */
    private $secretKey;

    /**
     * @var null|string
     */
    private $sessionToken;

    public function __construct(
        ?string $accessKeyId = null,
        ?string $secretKey = null,
        ?string $sessionToken = null,
        ?\DateTimeInterface $expiration = null,
    ) {
        $this->accessKeyId = $accessKeyId;
        $this->secretKey = $secretKey;
        $this->sessionToken = $sessionToken;
        $this->expiration = $expiration;
    }

    public function getAccessKeyId(): ?string
    {
        return $this->accessKeyId;
    }

    public function getExpiration(): ?\DateTimeInterface
    {
        return $this->expiration;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function getSessionToken(): ?string
    {
        return $this->sessionToken;
    }
}
