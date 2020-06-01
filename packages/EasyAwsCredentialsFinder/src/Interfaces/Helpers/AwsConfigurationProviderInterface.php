<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces\Helpers;

interface AwsConfigurationProviderInterface
{
    public function getCliPath(?string $path = null): string;

    public function getCurrentProfile(): string;

    /**
     * @return null|mixed[]
     */
    public function getCurrentProfileConfig(): ?array;
}
