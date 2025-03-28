<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Helper;

use AsyncAws\SecretsManager\SecretsManagerClient;

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
            foreach ((array)\json_decode($value ?? '{}', true) as $key => $value) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    public static function setSecretsManager(SecretsManagerClient $secretsManager): void
    {
        self::$secretsManager = $secretsManager;
    }
}
