<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws;

interface CredentialsProviderInterface
{
    /**
     * @return mixed[]
     */
    public function getCredentials(): array;

    public function getProfile(): ?string;
}
