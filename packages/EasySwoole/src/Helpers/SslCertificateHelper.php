<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use Symfony\Component\Filesystem\Filesystem;

final class SslCertificateHelper
{
    private const ENV_VAR_SSL_CERT = 'CERTIFICATE';

    private const ENV_VAR_SSL_KEY = 'PRIVATE_KEY_PEM';

    public static function loadSslCertificates(
        ?string $certFilename = null,
        ?string $keyFilename = null,
    ): void {
        $filesystem = new Filesystem();

        if ($certFilename !== null && $filesystem->exists($certFilename) === false) {
            self::saveEnvVarValueToFile(self::ENV_VAR_SSL_CERT, $filesystem, $certFilename);
        }

        if ($keyFilename !== null && $filesystem->exists($keyFilename) === false) {
            self::saveEnvVarValueToFile(self::ENV_VAR_SSL_KEY, $filesystem, $keyFilename);
        }
    }

    private static function saveEnvVarValueToFile(string $envVarName, Filesystem $filesystem, string $filename): void
    {
        $envVarValue = $_SERVER[$envVarName] ?? null;

        if (\is_string($envVarValue) && $envVarValue !== '') {
            $filesystem->dumpFile($filename, $envVarValue);
        }
    }
}
