<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Factories;

use EonX\EasyEncryption\Exceptions\CouldNotCreateEncryptionKeyException;
use EonX\EasyEncryption\Factories\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
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
            EncryptionKeyFactoryInterface::OPTION_KEY => $key,
            EncryptionKeyFactoryInterface::OPTION_SALT => $salt,
        ]);

        self::assertInstanceOf(EncryptionKey::class, $result);
    }

    public function testItSucceedsFromArrayWithSecretAndPublicKey(): void
    {
        $secretKey = 'key-must-be-either-16-or-32-byte';
        $publicKey = 'key-must-be-exactly-32-byte-long';
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create([
            EncryptionKeyFactoryInterface::OPTION_SECRET_KEY => $secretKey,
            EncryptionKeyFactoryInterface::OPTION_PUBLIC_KEY => $publicKey,
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
