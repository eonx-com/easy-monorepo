<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Common\Helper;

use EonX\EasySwoole\Common\Helper\SslCertificateHelper;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;

final class SslCertificateHelperTest extends AbstractUnitTestCase
{
    private const string KEY_ENV_VAR = 'EASY_SWOOLE_TEST_PRIVATE_KEY_PEM';

    protected function tearDown(): void
    {
        unset($_SERVER[self::KEY_ENV_VAR]);

        parent::tearDown();
    }

    public function testItSucceeds(): void
    {
        $keyFilename = __DIR__ . '/../../../../../var/ssl/key.pem';
        $_SERVER[self::KEY_ENV_VAR] = 'private-key';
        // The usual umask, which makes Filesystem::dumpFile() create 0644 files
        $umask = \umask(0o022);

        try {
            SslCertificateHelper::loadSslCertificates([
                'settings' => [
                    'ssl_cert_file' => __DIR__ . '/../../../../../var/ssl/cert.pem',
                    'ssl_key_file' => $keyFilename,
                ],
                'sock_type' => 1,
                'ssl_key_env_var_name' => self::KEY_ENV_VAR,
            ]);

            self::assertSame(0o022, \umask());
        } finally {
            \umask($umask);
        }

        self::assertSame('private-key', \file_get_contents($keyFilename));
        self::assertSame(0o600, \fileperms($keyFilename) & 0o777);
    }
}
