<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use OpenSwoole\Constant as OpenSwooleConstant;
use Swoole\Constant as SwooleConstant;
use Symfony\Component\Filesystem\Filesystem;

final class SslCertificateHelper
{
    private const DEFAULT_ENV_VAR_CERT = 'CERTIFICATE';

    private const DEFAULT_ENV_VAR_KEY = 'PRIVATE_KEY_PEM';

    private const DEFAULT_FILENAME_CERT = '/var/www/var/tmp/cert.pem';

    private const DEFAULT_FILENAME_KEY = '/var/www/var/tmp/key.pem';

    /**
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    public static function loadSslCertificates(array $options): array
    {
        $filesystem = new Filesystem();
        $certEnvVarName = $options['ssl_cert_env_var_name'] ?? self::DEFAULT_ENV_VAR_CERT;
        $keyEnvVarName = $options['ssl_key_env_var_name'] ?? self::DEFAULT_ENV_VAR_KEY;
        $certFilename = $options['settings'][SwooleConstant::OPTION_SSL_CERT_FILE] ?? self::DEFAULT_FILENAME_CERT;
        $keyFilename = $options['settings'][SwooleConstant::OPTION_SSL_KEY_FILE] ?? self::DEFAULT_FILENAME_KEY;

        $savedCert = self::saveEnvVarValueToFile($certEnvVarName, $filesystem, $certFilename);
        $savedKey = self::saveEnvVarValueToFile($keyEnvVarName, $filesystem, $keyFilename);

        if ($savedCert) {
            $options['settings'][SwooleConstant::OPTION_SSL_CERT_FILE] = $certFilename;
        }

        if ($savedKey) {
            $options['settings'][SwooleConstant::OPTION_SSL_KEY_FILE] = $keyFilename;
        }

        if ($savedCert || $savedKey) {
            $options['socket_type'] ??= \class_exists(OpenSwooleConstant::class)
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
