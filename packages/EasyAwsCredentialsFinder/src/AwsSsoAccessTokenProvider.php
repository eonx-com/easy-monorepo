<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder;

use Carbon\Carbon;
use EonX\EasyAwsCredentialsFinder\Exceptions\InvalidCachedSsoAccessTokenContentsException;
use EonX\EasyAwsCredentialsFinder\Exceptions\SsoAccessTokenNotFoundException;
use EonX\EasyAwsCredentialsFinder\Interfaces\AwsSsoAccessTokenInterface;
use EonX\EasyAwsCredentialsFinder\Interfaces\AwsSsoAccessTokenProviderInterface;
use EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\AwsConfigurationProviderInterface;
use Symfony\Component\Filesystem\Filesystem;

final class AwsSsoAccessTokenProvider implements AwsSsoAccessTokenProviderInterface
{
    /**
     * @var string[]
     */
    private static $mustBeEqual = [
        'startUrl' => 'sso_start_url',
        'region' => 'sso_region',
    ];

    /**
     * @var string[]
     */
    private static $requiredCachedContents = ['startUrl', 'region', 'accessToken', 'expiresAt'];

    /**
     * @var \EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\AwsConfigurationProviderInterface
     */
    private $configProvider;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    public function __construct(AwsConfigurationProviderInterface $configProvider, Filesystem $filesystem)
    {
        $this->configProvider = $configProvider;
        $this->filesystem = $filesystem;
    }

    public function getSsoAccessToken(): AwsSsoAccessTokenInterface
    {
        $ssoConfigs = $this->configProvider->getCurrentProfileSsoConfig() ?? [];
        $ssoCacheKey = $this->configProvider->computeSsoAccessTokenCacheKey($ssoConfigs);

        $filename = $this->configProvider->getCliPath(\sprintf('sso/cache/%s.json', $ssoCacheKey));

        if ($this->filesystem->exists($filename) === false) {
            throw new SsoAccessTokenNotFoundException(\sprintf(
                'No SSO access token cache file found for startUrl "%s". Please run "aws sso login" first.',
                $ssoConfigs['sso_start_url'] ?? ''
            ));
        }

        $cachedAccessToken = \json_decode((string)\file_get_contents($filename), true) ?? [];

        $this->validateCachedContents($cachedAccessToken, $ssoConfigs);

        $expiration = new Carbon($cachedAccessToken['expiresAt']);

        if ($expiration->isPast()) {
            throw new InvalidCachedSsoAccessTokenContentsException(
                'Cached SSO access token found but expired. Please run "aws sso login" first.'
            );
        }

        return new AwsSsoAccessToken(
            $cachedAccessToken['accessToken'],
            $expiration,
            $cachedAccessToken['region'],
            $cachedAccessToken['startUrl']
        );
    }

    /**
     * @param mixed[] $cachedAccessToken
     * @param mixed[] $ssoConfigs
     *
     * @throws \EonX\EasyAwsCredentialsFinder\Exceptions\InvalidCachedSsoAccessTokenContentsException
     */
    private function validateCachedContents(array $cachedAccessToken, array $ssoConfigs): void
    {
        $missing = [];
        $notEqual = [];

        foreach (self::$requiredCachedContents as $key) {
            if (isset($cachedAccessToken[$key]) === false) {
                $missing[] = $key;
            }
        }

        foreach (self::$mustBeEqual as $cache => $config) {
            if ($cachedAccessToken[$cache] !== $ssoConfigs[$config]) {
                $notEqual[] = $cache;
            }
        }

        if (empty($missing) === false) {
            throw new InvalidCachedSsoAccessTokenContentsException(\sprintf(
                'Cached SSO access token contents invalid. Missing ["%s"].',
                \implode('","', $missing)
            ));
        }

        if (empty($notEqual) === false) {
            throw new InvalidCachedSsoAccessTokenContentsException(\sprintf(
                'Cached SSO access token contents invalid. Not matching config values ["%s"].',
                \implode('","', $notEqual)
            ));
        }
    }
}
