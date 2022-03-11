<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Helpers;

use Aws\AbstractConfigurationProvider;
use EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\AwsConfigurationProviderInterface;

final class AwsConfigurationProvider extends AbstractConfigurationProvider implements AwsConfigurationProviderInterface
{
    /**
     * @var string[]
     */
    private static $ssoConfigs = [
        'sso_start_url' => 'startUrl',
        'sso_role_name' => 'roleName',
        'sso_account_id' => 'accountId',
    ];

    /**
     * @param mixed[] $ssoConfigs
     */
    public function computeCliCredentialsSsoCacheKey(array $ssoConfigs): string
    {
        $args = [];

        foreach (self::$ssoConfigs as $snake => $studly) {
            $args[$studly] = $ssoConfigs[$snake] ?? '';
        }

        \ksort($args);

        return \sha1(\utf8_encode((string)\json_encode($args, \JSON_UNESCAPED_SLASHES)));
    }

    /**
     * @param mixed[] $ssoConfigs
     */
    public function computeSsoAccessTokenCacheKey(array $ssoConfigs): string
    {
        return \sha1(\utf8_encode($ssoConfigs['sso_start_url'] ?? ''));
    }

    public function getCliPath(?string $path = null): string
    {
        $home = static::getHomeDir();

        return empty($path) ? (string)$home : \sprintf('%s/.aws/%s', $home, $path);
    }

    public function getCurrentProfile(): string
    {
        $profile = \getenv('AWS_PROFILE');

        if ($profile !== false) {
            return $profile;
        }

        return 'default';
    }

    /**
     * @return null|mixed[]
     */
    public function getCurrentProfileConfig(): ?array
    {
        $profile = \sprintf('profile %s', $this->getCurrentProfile());
        $parsed = $this->parseConfig();

        foreach ($parsed as $name => $config) {
            if ($profile === $name) {
                return $config;
            }
        }

        return null;
    }

    /**
     * @return null|mixed[]
     */
    public function getCurrentProfileSsoConfig(): ?array
    {
        $config = $this->getCurrentProfileConfig();

        if ($config === null) {
            return null;
        }

        foreach (\array_keys(self::$ssoConfigs) as $ssoConfig) {
            if (isset($config[$ssoConfig]) === false) {
                return null;
            }
        }

        return $config;
    }

    /**
     * @return mixed[]
     */
    private function parseConfig(?string $path = null): array
    {
        $filename = $this->getCliPath($path ?? 'config');

        if (\is_file($filename) === false) {
            return [];
        }

        $parsed = \Aws\parse_ini_file($filename, true);

        return \is_array($parsed) ? $parsed : [];
    }
}
