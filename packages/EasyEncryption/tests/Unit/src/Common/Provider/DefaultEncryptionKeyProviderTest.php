<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Common\Provider;

use EonX\EasyEncryption\Common\Exception\CircularReferenceDetectedException;
use EonX\EasyEncryption\Common\Exception\CouldNotProvideEncryptionKeyException;
use EonX\EasyEncryption\Common\Factory\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Common\Provider\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Tests\Stub\Resolver\EncryptionKeyResolverStub;
use EonX\EasyEncryption\Tests\Unit\AbstractSymfonyTestCase;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

final class DefaultEncryptionKeyProviderTest extends AbstractSymfonyTestCase
{
    use PrivatePropertyAccessTrait;

    public function testGetKeySucceeds(): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $value = 'key-must-be-exactly-32-byte-long';
        $encryptionKey = new EncryptionKey(new HiddenString($value));
        $resolver = new EncryptionKeyResolverStub(['some-key' => $encryptionKey]);
        $sut = new DefaultEncryptionKeyProvider($keyFactory, [$resolver]);

        $result = $sut->getKey('some-key');

        self::assertInstanceOf(EncryptionKey::class, $result);
        self::assertSame($value, $result->getRawKeyMaterial());
    }

    public function testGetKeySucceedsForAlreadyResolvedValue(): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $value = 'key-must-be-exactly-32-byte-long';
        $encryptionKey = new EncryptionKey(new HiddenString($value));
        $sut = new DefaultEncryptionKeyProvider($keyFactory, []);
        self::setPrivatePropertyValue($sut, 'resolved', ['some-key' => $encryptionKey]);

        $result = $sut->getKey('some-key');

        self::assertInstanceOf(EncryptionKey::class, $result);
        self::assertSame($value, $result->getRawKeyMaterial());
    }

    public function testGetKeyThrowsExceptionIfKeyResolverIsNotFound(): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $sut = new DefaultEncryptionKeyProvider($keyFactory, []);

        $this->expectException(CouldNotProvideEncryptionKeyException::class);
        $this->expectExceptionMessage('Could not provide encryption key: No resolver found for key "some-key"');

        $sut->getKey('some-key');
    }

    public function testGetKeyThrowsExceptionInCaseOfCircularReference(): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $value = 'key-must-be-exactly-32-byte-long';
        $encryptionKey = new EncryptionKey(new HiddenString($value));
        $sut = new DefaultEncryptionKeyProvider($keyFactory, []);
        self::setPrivatePropertyValue($sut, 'resolving', ['some-key' => $encryptionKey]);

        $this->expectException(CircularReferenceDetectedException::class);
        $this->expectExceptionMessage('Circular reference detected for key "some-key"');

        $sut->getKey('some-key');
    }

    public function testHasKeyReturnsFalse(): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $sut = new DefaultEncryptionKeyProvider($keyFactory, []);

        $result = $sut->hasKey('some-key');

        self::assertFalse($result);
    }

    public function testHasKeyReturnsTrue(): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $resolver = new EncryptionKeyResolverStub(['some-key' => 'some-value']);
        $sut = new DefaultEncryptionKeyProvider($keyFactory, [$resolver]);

        $result = $sut->hasKey('some-key');

        self::assertTrue($result);
    }

    public function testResetSucceeds(): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $sut = new DefaultEncryptionKeyProvider($keyFactory, []);
        self::setPrivatePropertyValue($sut, 'resolved', ['some-key' => 'some-value']);

        $sut->reset();

        self::assertSame([], self::getPrivatePropertyValue($sut, 'resolved'));
    }
}
