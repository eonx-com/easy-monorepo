<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use OpenSwoole\Constant as OpenSwooleConstant;
use Symfony\Component\Filesystem\Filesystem;

final class SslCertificateHelper
{
    private const DEFAULT_ENV_VAR_CERT = 'CERTIFICATE';

    private const DEFAULT_ENV_VAR_KEY = 'PRIVATE_KEY_PEM';

    private const DEFAULT_FILENAME_CERT = '/var/www/var/tmp/cert.pem';

    private const DEFAULT_FILENAME_KEY = '/var/www/var/tmp/key.pem';

    public static function loadSslCertificates(array $options): array
    {
        $filesystem = new Filesystem();
        $certEnvVarName = $options['ssl_cert_env_var_name'] ?? self::DEFAULT_ENV_VAR_CERT;
        $keyEnvVarName = $options['ssl_key_env_var_name'] ?? self::DEFAULT_ENV_VAR_KEY;
        $certFilename = $options['settings']['ssl_cert_file'] ?? self::DEFAULT_FILENAME_CERT;
        $keyFilename = $options['settings']['ssl_key_file'] ?? self::DEFAULT_FILENAME_KEY;

        $savedCert = self::saveEnvVarValueToFile($certEnvVarName, $filesystem, $certFilename);
        $savedKey = self::saveEnvVarValueToFile($keyEnvVarName, $filesystem, $keyFilename);

        if ($savedCert) {
            $options['settings']['ssl_cert_file'] = $certFilename;
        }

        if ($savedKey) {
            $options['settings']['ssl_key_file'] = $keyFilename;
        }

        if ($savedCert || $savedKey) {
            $options['sock_type'] ??= \class_exists(OpenSwooleConstant::class)
                ? OpenSwooleConstant::SOCK_TCP | OpenSwooleConstant::SSL
                : \SWOOLE_SOCK_TCP | \SWOOLE_SSL;
        }

        return $options;
    }

    private static function saveEnvVarValueToFile(string $envVarName, Filesystem $filesystem, string $filename): bool
    {
        if ($filesystem->exists($filename)) {
            return true;
        }

        $envVarValue = $_SERVER[$envVarName] ?? $_ENV[$envVarName] ?? null;

        if (\is_string($envVarValue) && $envVarValue !== '') {
            $filesystem->dumpFile($filename, $envVarValue);

            return true;
        }

        return false;
    }
}
