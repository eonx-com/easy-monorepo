<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Finders;

use Carbon\Carbon;
use EonX\EasyAwsCredentialsFinder\AwsCredentials;
use EonX\EasyAwsCredentialsFinder\Interfaces\AwsCredentialsInterface;
use EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\AwsConfigurationProviderInterface;
use EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\ProcessRunnerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CliSsoCachedCredentialsFinder extends AbstractAwsCredentialsFinder
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
     * @var \EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\AwsConfigurationProviderInterface
     */
    private $configProvider;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\ProcessRunnerInterface
     */
    private $processRunner;

    public function __construct(
        AwsConfigurationProviderInterface $configProvider,
        Filesystem $filesystem,
        ProcessRunnerInterface $processRunner,
        ?int $priority = null
    ) {
        $this->configProvider = $configProvider;
        $this->filesystem = $filesystem;
        $this->processRunner = $processRunner;

        parent::__construct($priority);
    }

    public function findCredentials(): ?AwsCredentialsInterface
    {
        $ssoConfig = $this->getSsoConfig();

        if ($ssoConfig === null) {
            return null;
        }

        $cacheKey = $this->getCacheKey($ssoConfig);
        $filename = $this->configProvider->getCliPath(\sprintf('cli/cache/%s.json', $cacheKey));

        if ($this->filesystem->exists($filename)) {
            return $this->parseCredentials($filename);
        }

        // Cache file doesn't exist, let's use the aws cli to generate it
        $this->refreshCliCache();

        if ($this->filesystem->exists($filename)) {
            return $this->parseCredentials($filename);
        }

        return null;
    }

    /**
     * @param mixed[] $ssoConfig
     */
    private function getCacheKey(array $ssoConfig): string
    {
        $args = [];

        foreach (static::$ssoConfigs as $snake => $studly) {
            $args[$studly] = $ssoConfig[$snake];
        }

        \ksort($args);

        return \sha1(\utf8_encode((string)\json_encode($args, \JSON_UNESCAPED_SLASHES)));
    }

    /**
     * @return null|mixed[]
     */
    private function getSsoConfig(): ?array
    {
        $config = $this->configProvider->getCurrentProfileConfig();

        if ($config === null) {
            return null;
        }

        foreach (\array_keys(static::$ssoConfigs) as $ssoConfig) {
            if (isset($config[$ssoConfig]) === false) {
                return null;
            }
        }

        return $config;
    }

    private function parseCredentials(string $filename): AwsCredentialsInterface
    {
        $contents = \json_decode((string)\file_get_contents($filename), true);

        // Assume Expiration is always there, if not then the cli changed its logic
        $expiration = new Carbon($contents['Credentials']['Expiration']);

        // Refresh cli cache because credentials expired
        if ($expiration->isPast()) {
            $this->refreshCliCache();

            $contents = \json_decode((string)\file_get_contents($filename), true);
            $expiration = new Carbon($contents['Credentials']['Expiration']);
        }

        return new AwsCredentials(
            $contents['Credentials']['AccessKeyId'] ?? null,
            $contents['Credentials']['SecretAccessKey'] ?? null,
            $contents['Credentials']['SessionToken'] ?? null,
            $expiration,
        );
    }

    private function refreshCliCache(): void
    {
        $this->processRunner->run(['aws', 's3', 'ls']);
    }
}
