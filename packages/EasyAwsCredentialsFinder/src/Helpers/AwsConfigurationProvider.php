<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Helpers;

use Aws\AbstractConfigurationProvider;
use EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\AwsConfigurationProviderInterface;

final class AwsConfigurationProvider extends AbstractConfigurationProvider implements AwsConfigurationProviderInterface
{
    public function getCliPath(?string $path = null): string
    {
        $home = static::getHomeDir();

        return empty($path) ? (string)$home : \sprintf('%s/.aws/%s', $home, $path);
    }

    public function getCurrentProfile(): string
    {
        if ($profile = \getenv('AWS_PROFILE')) {
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
     * @return mixed[]
     */
    private function parseConfig(?string $path = null): array
    {
        $parsed = \Aws\parse_ini_file($this->getCliPath($path ?? 'config'), true);

        return \is_array($parsed) ? $parsed : [];
    }
}
