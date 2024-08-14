<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Common\Factory;

use EonX\EasyEncryption\Common\Enum\EncryptionKeyOption;
use EonX\EasyEncryption\Common\Exception\CouldNotCreateEncryptionKeyException;
use EonX\EasyEncryption\Common\Factory\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Tests\Unit\AbstractSymfonyTestCase;
use ParagonIE\Halite\Asymmetric\EncryptionSecretKey;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

final class DefaultEncryptionKeyFactoryTest extends AbstractSymfonyTestCase
{
    public function testItSucceedsFromArrayWithKeyAndSalt(): void
    {
        $key = 'some-key';
        $salt = 'must-be-16-bytes';
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create([
            EncryptionKeyOption::Key->value => $key,
            EncryptionKeyOption::Salt->value => $salt,
        ]);

        self::assertInstanceOf(EncryptionKey::class, $result);
    }

    public function testItSucceedsFromArrayWithSecretAndPublicKey(): void
    {
        $secretKey = 'key-must-be-either-16-or-32-byte';
        $publicKey = 'key-must-be-exactly-32-byte-long';
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create([
            EncryptionKeyOption::SecretKey->value => $secretKey,
            EncryptionKeyOption::PublicKey->value => $publicKey,
        ]);

        self::assertInstanceOf(EncryptionKeyPair::class, $result);
        self::assertSame($secretKey, $result->getSecretKey()->getRawKeyMaterial());
    }

    public function testItSucceedsFromEncryptionKey(): void
    {
        $value = 'key-must-be-exactly-32-byte-long';
        $key = new EncryptionKey(new HiddenString($value));
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create($key);

        self::assertSame($key, $result);
    }

    public function testItSucceedsFromEncryptionKeyPair(): void
    {
        $value = 'key-must-be-exactly-32-byte-long';
        $key = new EncryptionKeyPair(new EncryptionSecretKey(new HiddenString($value)));
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create($key);

        self::assertSame($key, $result);
    }

    public function testItSucceedsFromString(): void
    {
        $key = 'key-must-be-exactly-32-byte-long';
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create($key);

        self::assertInstanceOf(EncryptionKey::class, $result);
        self::assertSame($key, $result->getRawKeyMaterial());
    }

    public function testItThrowsExceptionForArrayIfKeyIsNotSupported(): void
    {
        $key = 'some-key';
        $sut = new DefaultEncryptionKeyFactory();

        $this->expectException(CouldNotCreateEncryptionKeyException::class);
        $this->expectExceptionMessage('Could not create encryption key: Could not identify key type from given array');

        $sut->create(['key' => $key]);
    }

    public function testItThrowsExceptionForStringIfKeyIsNotSupported(): void
    {
        $key = 'some-key';
        $sut = new DefaultEncryptionKeyFactory();

        $this->expectException(CouldNotCreateEncryptionKeyException::class);
        $this->expectExceptionMessage('Could not create encryption key: Could not identify key type from given string');

        $sut->create($key);
    }
}
