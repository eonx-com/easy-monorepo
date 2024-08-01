<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Providers;

use EonX\EasyEncryption\Exceptions\CircularReferenceDetectedException;
use EonX\EasyEncryption\Exceptions\CouldNotProvideEncryptionKeyException;
use EonX\EasyEncryption\Factories\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Providers\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyEncryption\Tests\Stubs\EncryptionKeyResolverStub;
use EonX\EasyTest\Traits\PrivatePropertyAccessTrait;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

final class DefaultEncryptionKeyProviderTest extends AbstractSymfonyTestCase
{
    use PrivatePropertyAccessTrait;

    public function testGetKeySucceeds()
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $value = 'key-must-be-exactly-32-byte-long';
        $encryptionKey = new EncryptionKey(new HiddenString($value));
        $resolver = new EncryptionKeyResolverStub(['some-key' => $encryptionKey]);
        $sut = new DefaultEncryptionKeyProvider($keyFactory, [$resolver]);

        $result = $sut->getKey('some-key');

        self::assertSame($value, $result->getRawKeyMaterial());
    }

    public function testGetKeySucceedsForAlreadyResolvedValue()
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $value = 'key-must-be-exactly-32-byte-long';
        $encryptionKey = new EncryptionKey(new HiddenString($value));
        $sut = new DefaultEncryptionKeyProvider($keyFactory, []);
        self::setPrivatePropertyValue($sut, 'resolved', ['some-key' => $encryptionKey]);

        $result = $sut->getKey('some-key');

        self::assertSame($value, $result->getRawKeyMaterial());
    }

    public function testGetKeyThrowsExceptionIfKeyResolverIsNotFound()
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $sut = new DefaultEncryptionKeyProvider($keyFactory, []);

        $this->expectException(CouldNotProvideEncryptionKeyException::class);
        $this->expectExceptionMessage('Could not provide encryption key: No resolver found for key "some-key"');

        $sut->getKey('some-key');
    }

    public function testGetKeyThrowsExceptionInCaseOfCircularReference()
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

    public function testHasKeyReturnsFalse()
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $sut = new DefaultEncryptionKeyProvider($keyFactory, []);

        $result = $sut->hasKey('some-key');

        self::assertFalse($result);
    }

    public function testHasKeyReturnsTrue()
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
