<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces;

interface AwsCredentialsInterface
{
    public function getAccessKeyId(): ?string;

    public function getExpiration(): ?\DateTimeInterface;

    public function getSecretKey(): ?string;

    public function getSessionToken(): ?string;
}
