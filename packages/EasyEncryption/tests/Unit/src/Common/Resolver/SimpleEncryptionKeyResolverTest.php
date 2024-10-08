<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Common\Resolver;

use EonX\EasyEncryption\Common\Exception\CouldNotResolveEncryptionKeyException;
use EonX\EasyEncryption\Common\Resolver\SimpleEncryptionKeyResolver;
use EonX\EasyEncryption\Tests\Unit\AbstractSymfonyTestCase;

final class SimpleEncryptionKeyResolverTest extends AbstractSymfonyTestCase
{
    public function testItSucceeds(): void
    {
        $keyName = 'some-key';
        $encryptionKey = 'key-must-be-either-16-or-32-byte';
        $sut = new SimpleEncryptionKeyResolver($keyName, $encryptionKey);

        $result = $sut->resolveKey('some-key');

        self::assertSame($encryptionKey, $result);
    }

    public function testItSucceedsWithSalt(): void
    {
        $keyName = 'some-key';
        $encryptionKey = 'short-key';
        $salt = 'must-be-16-bytes';
        $sut = new SimpleEncryptionKeyResolver($keyName, $encryptionKey, $salt);

        $result = $sut->resolveKey('some-key');

        self::assertSame(['key' => 'short-key', 'salt' => 'must-be-16-bytes'], $result);
    }

    public function testItThrowsExceptionForShortKeyWithoutSalt(): void
    {
        $keyName = 'some-key';
        $encryptionKey = 'short-key';
        $sut = new SimpleEncryptionKeyResolver($keyName, $encryptionKey);

        $this->expectException(CouldNotResolveEncryptionKeyException::class);
        $this->expectExceptionMessage(
            'Given key must be either 16 or 32 bytes. Any other length requires a salt to be given'
        );

        $sut->resolveKey('some-key');
    }

    public function testItThrowsExceptionIfKeyIsNotSupported(): void
    {
        $keyName = 'some-key';
        $encryptionKey = 'key-must-be-either-16-or-32-byte';
        $sut = new SimpleEncryptionKeyResolver($keyName, $encryptionKey);

        $this->expectException(CouldNotResolveEncryptionKeyException::class);
        $this->expectExceptionMessage(
            'Given key name "unsupported-key" not supported by ' . SimpleEncryptionKeyResolver::class
        );

        $sut->resolveKey('unsupported-key');
    }
}
