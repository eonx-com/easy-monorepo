<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces\Helpers;

interface AwsConfigurationProviderInterface
{
    /**
     * @param mixed[] $ssoConfigs
     */
    public function computeCliCredentialsSsoCacheKey(array $ssoConfigs): string;

    /**
     * @param mixed[] $ssoConfigs
     */
    public function computeSsoAccessTokenCacheKey(array $ssoConfigs): string;

    public function getCliPath(?string $path = null): string;

    public function getCurrentProfile(): string;

    /**
     * @return null|mixed[]
     */
    public function getCurrentProfileConfig(): ?array;

    /**
     * @return null|mixed[]
     */
    public function getCurrentProfileSsoConfig(): ?array;
}
