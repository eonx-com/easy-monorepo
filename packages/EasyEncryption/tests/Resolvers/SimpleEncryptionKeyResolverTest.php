<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Resolvers;

use EonX\EasyEncryption\Exceptions\CouldNotResolveEncryptionKeyException;
use EonX\EasyEncryption\Resolvers\SimpleEncryptionKeyResolver;
use EonX\EasyEncryption\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class SimpleEncryptionKeyResolverTest extends AbstractSymfonyTestCase
{
    public function testItSucceeds(): void
    {
        $keyName = 'some-key';
        $encryptionKey = 'key-must-be-either-16-or-32-byte';
        $resolver = new SimpleEncryptionKeyResolver($keyName, $encryptionKey);

        $result = $resolver->resolveKey('some-key');

        self::assertSame($encryptionKey, $result);
    }

    public function testItSucceedsWithSalt(): void
    {
        $keyName = 'some-key';
        $encryptionKey = 'short-key';
        $salt = 'must-be-16-bytes';
        $resolver = new SimpleEncryptionKeyResolver($keyName, $encryptionKey, $salt);

        $result = $resolver->resolveKey('some-key');

        self::assertSame(['key' => 'short-key',    'salt' => 'must-be-16-bytes'], $result);
    }

    public function testItThrowsExceptionForShortKeyWithoutSalt(): void
    {
        $keyName = 'some-key';
        $encryptionKey = 'short-key';
        $resolver = new SimpleEncryptionKeyResolver($keyName, $encryptionKey);

        $this->expectException(CouldNotResolveEncryptionKeyException::class);
        $this->expectExceptionMessage(
            'Given key must be either 16 or 32 bytes. Any other length requires a salt to be given'
        );

        $resolver->resolveKey('some-key');
    }
}
