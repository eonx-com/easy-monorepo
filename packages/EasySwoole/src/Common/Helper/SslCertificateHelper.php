<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Helper;

use OpenSwoole\Constant as OpenSwooleConstant;
use Symfony\Component\Filesystem\Filesystem;

final class SslCertificateHelper
{
    private const string DEFAULT_ENV_VAR_CERT = 'CERTIFICATE';

    private const string DEFAULT_ENV_VAR_KEY = 'PRIVATE_KEY_PEM';

    private const string DEFAULT_FILENAME_CERT = '/var/www/var/tmp/cert.pem';

    private const string DEFAULT_FILENAME_KEY = '/var/www/var/tmp/key.pem';

    /**
     * Filesystem::dumpFile() writes to a private temporary file, sets its mode to 0666 & ~umask and renames it
     * into place, so with this umask the key file is never readable by others, not even for a moment
     */
    private const int PRIVATE_FILE_UMASK = 0o077;

    public static function loadSslCertificates(array $options): array
    {
        $filesystem = new Filesystem();
        $certEnvVarName = $options['ssl_cert_env_var_name'] ?? self::DEFAULT_ENV_VAR_CERT;
        $keyEnvVarName = $options['ssl_key_env_var_name'] ?? self::DEFAULT_ENV_VAR_KEY;
        $certFilename = $options['settings']['ssl_cert_file'] ?? self::DEFAULT_FILENAME_CERT;
        $keyFilename = $options['settings']['ssl_key_file'] ?? self::DEFAULT_FILENAME_KEY;

        $savedCert = self::saveEnvVarValueToFile($certEnvVarName, $filesystem, $certFilename);
        $savedKey = self::saveEnvVarValueToFile($keyEnvVarName, $filesystem, $keyFilename, true);

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

    private static function saveEnvVarValueToFile(
        string $envVarName,
        Filesystem $filesystem,
        string $filename,
        bool $private = false,
    ): bool {
        if ($filesystem->exists($filename)) {
            return true;
        }

        $envVarValue = $_SERVER[$envVarName] ?? $_ENV[$envVarName] ?? null;

        if (\is_string($envVarValue) && $envVarValue !== '') {
            // Runs once on startup, before the server forks workers, so the process-wide umask is safe to change
            $umask = $private ? \umask(self::PRIVATE_FILE_UMASK) : null;

            try {
                $filesystem->dumpFile($filename, $envVarValue);
            } finally {
                if ($umask !== null) {
                    \umask($umask);
                }
            }

            return true;
        }

        return false;
    }
}
