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
    public function testItSucceedsFromArray(): void
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

    public function testItSucceedsFromEncryptionKey(): void
    {
        $value = 'key-must-be-either-16-or-32-byte';
        $key = new EncryptionKey(new HiddenString($value));
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create($key);

        self::assertSame($key, $result);
    }

    public function testItSucceedsFromEncryptionKeyPair(): void
    {
        $value = 'key-must-be-either-16-or-32-byte';
        $key = new EncryptionKeyPair(new EncryptionSecretKey(new HiddenString($value)));
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create($key);

        self::assertSame($key, $result);
    }

    public function testItSucceedsFromString(): void
    {
        $key = 'key-must-be-either-16-or-32-byte';
        $sut = new DefaultEncryptionKeyFactory();

        $result = $sut->create($key);

        self::assertInstanceOf(EncryptionKey::class, $result);
        self::assertSame($key, $result->getRawKeyMaterial());
    }

    public function testItThrowsExceptionIfKeyIsNotSupported(): void
    {
        $key = 'some-key';
        $sut = new DefaultEncryptionKeyFactory();

        $this->expectException(CouldNotCreateEncryptionKeyException::class);
        $this->expectExceptionMessage('Could not create encryption key: Could not identify key type from given string');

        $sut->create($key);
    }
}
