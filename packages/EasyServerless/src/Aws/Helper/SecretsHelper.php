<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Helper;

use AsyncAws\SecretsManager\SecretsManagerClient;
use Symfony\Component\Finder\Finder;

final class SecretsHelper
{
    private const ALREADY_LOADED = 'easy_serverless.secrets.already_loaded';

    private const PREFIX_SECRETS_MANAGER = 'resolve:secretsmanager:';

    private static ?SecretsManagerClient $secretsManager = null;

    public static function load(): void
    {
        // Some cases will trigger the startup logic multiple times, such as scheduler events
        if (isset($_SERVER[self::ALREADY_LOADED])) {
            return;
        }

        self::logToStderr('Start loading secrets...');

        self::doLoad(\array_filter(
            $_SERVER,
            static fn ($value): bool => \is_string($value) && \str_starts_with($value, self::PREFIX_SECRETS_MANAGER)
        ));

        $_SERVER[self::ALREADY_LOADED] = true;
    }

    public static function loadFromJsonFiles(string $dir): void
    {
        $files = (new Finder())
            ->in($dir)
            ->files()
            ->name('*.json');

        foreach ($files as $file) {
            if (\json_validate($file->getContents()) === false) {
                continue;
            }

            self::doLoad((array)\json_decode($file->getContents(), true));
        }
    }

    public static function loadFromSecretsManager(string $paramName): void
    {
        self::$secretsManager ??= new SecretsManagerClient();

        if (\str_starts_with($paramName, self::PREFIX_SECRETS_MANAGER)) {
            $paramName = \str_replace(self::PREFIX_SECRETS_MANAGER, '', $paramName);
        }

        $input = ['SecretId' => $paramName];

        // Support specific versionId as <SecretId>:<VersionId>
        if (\str_contains($paramName, ':')) {
            $exploded = \explode(':', $paramName);

            $input = [
                'SecretId' => $exploded[0],
                'VersionId' => $exploded[1],
            ];
        }

        $value = self::$secretsManager
            ->getSecretValue($input)
            ->getSecretString();

        if (\json_validate($value ?? '')) {
            self::doLoad((array)\json_decode($value ?? '{}', true));
        }
    }

    private static function doLoad(array $envVars): void
    {
        foreach ($envVars as $key => $value) {
            if (\is_string($value) && \str_starts_with($value, self::PREFIX_SECRETS_MANAGER)) {
                self::logToStderr(\sprintf('Found secret to resolve from SecretsManager: %s => %s', $key, $value));

                self::loadFromSecretsManager($value);

                continue;
            }

            self::logToStderr(\sprintf('Loading secret %s...', $key));

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private static function logToStderr(string $message): void
    {
        \file_put_contents('php://stderr', \date('[c] ') . $message . \PHP_EOL, \FILE_APPEND);
    }
}
