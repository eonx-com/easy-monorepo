<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Helper;

use AsyncAws\SecretsManager\SecretsManagerClient;
use Symfony\Component\Finder\Finder;

final class SecretsHelper
{
    private static ?SecretsManagerClient $secretsManager = null;

    public static function load(string $paramName): void
    {
        self::$secretsManager ??= new SecretsManagerClient();

        $value = self::$secretsManager
            ->getSecretValue(['SecretId' => $paramName])
            ->getSecretString();

        if (\json_validate($value ?? '')) {
            self::doLoad((array)\json_decode($value ?? '{}', true));
        }
    }

    public static function loadFromEnvWithPrefix(string $prefix): void
    {
        foreach ($_SERVER as $name => $value) {
            if (\str_starts_with($name, $prefix)) {
                self::doLoad($value);
            }
        }
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

    public static function setSecretsManager(SecretsManagerClient $secretsManager): void
    {
        self::$secretsManager = $secretsManager;
    }

    private static function doLoad(array $envVars): void
    {
        foreach ($envVars as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
