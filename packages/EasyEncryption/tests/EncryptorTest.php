<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests;

use EonX\EasyEncryption\Encryptor;
use EonX\EasyEncryption\Factories\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\Providers\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Resolvers\SimpleEncryptionKeyResolver;
use EonX\EasyEncryption\Tests\Stubs\EncryptionKeyResolverStub;
use ParagonIE\Halite\KeyFactory;

final class EncryptorTest extends AbstractTestCase
{
    /**
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \SodiumException
     *
     * @see testEncrypt
     */
    public static function providerTestEncrypt(): iterable
    {
        $message = 'My message';

        yield 'From resolver encryption key as string' => [
            $message,
            null,
            [
                new EncryptionKeyResolverStub([
                    EncryptorInterface::DEFAULT_KEY_NAME => KeyFactory::generateEncryptionKey()->getRawKeyMaterial(),
                ]),
            ],
        ];

        yield 'From resolver encryption secret key as string' => [
            $message,
            null,
            [
                new EncryptionKeyResolverStub([
                    EncryptorInterface::DEFAULT_KEY_NAME => KeyFactory::generateEncryptionKeyPair()
                        ->getSecretKey()
                        ->getRawKeyMaterial(),
                ]),
            ],
        ];

        yield 'From resolver key + salt as array' => [
            $message,
            null,
            [
                new EncryptionKeyResolverStub([
                    EncryptorInterface::DEFAULT_KEY_NAME => [
                        EncryptionKeyFactoryInterface::OPTION_KEY => 'IP70gUSWQ3qhl1Nf',
                        EncryptionKeyFactoryInterface::OPTION_SALT => 'xOI68z0AeksgNYAm',
                    ],
                ]),
            ],
        ];
    }

    /**
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \SodiumException
     *
     * @see testEncryptRaw
     */
    public static function providerTestEncryptRaw(): iterable
    {
        $message = 'My message';

        yield 'Direct encryption key as string' => [
            $message,
            KeyFactory::generateEncryptionKey()->getRawKeyMaterial(),
        ];

        yield 'Direct encryption secret key as string' => [
            $message,
            KeyFactory::generateEncryptionKeyPair()->getSecretKey()->getRawKeyMaterial(),
        ];

        yield 'Direct key + salt as array' => [
            $message,
            [
                EncryptionKeyFactoryInterface::OPTION_KEY => 'IP70gUSWQ3qhl1Nf',
                EncryptionKeyFactoryInterface::OPTION_SALT => 'xOI68z0AeksgNYAm',
            ],
        ];

        $encryptionKeyPair = KeyFactory::generateEncryptionKeyPair();
        yield 'Direct public + secret as array' => [
            $message,
            [
                EncryptionKeyFactoryInterface::OPTION_PUBLIC_KEY => $encryptionKeyPair->getPublicKey()
                    ->getRawKeyMaterial(),
                EncryptionKeyFactoryInterface::OPTION_SECRET_KEY => $encryptionKeyPair->getSecretKey()
                    ->getRawKeyMaterial(),
            ],
        ];

        yield 'Direct key + salt (same) as array' => [
            $message,
            [
                EncryptionKeyFactoryInterface::OPTION_KEY => 'IP70gUSWQ3qhl1Nf',
                EncryptionKeyFactoryInterface::OPTION_SALT => 'IP70gUSWQ3qhl1Nf',
            ],
        ];

        yield 'From simple resolver key itself' => [
            $message,
            'myKeyName',
            [
                new SimpleEncryptionKeyResolver('myKeyName', 'f42a3968db6a957300c4f0c46a341c80'),
            ],
        ];
    }

    /**
     * @param \EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface[]|null $resolvers
     *
     * @dataProvider providerTestEncrypt
     */
    public function testEncrypt(string $text, mixed $key = null, ?array $resolvers = null): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $keyProvider = new DefaultEncryptionKeyProvider($keyFactory, $resolvers ?? []);
        $encryptor = new Encryptor($keyFactory, $keyProvider);

        $encrypted = $encryptor->encrypt($text, $key);

        self::assertEquals($text, (string)$encryptor->decrypt($encrypted));
    }

    /**
     * @param \EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface[]|null $resolvers
     *
     * @dataProvider providerTestEncryptRaw
     */
    public function testEncryptRaw(string $text, mixed $key = null, ?array $resolvers = null): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $keyProvider = new DefaultEncryptionKeyProvider($keyFactory, $resolvers ?? []);
        $encryptor = new Encryptor($keyFactory, $keyProvider);

        $encrypted = $encryptor->encryptRaw($text, $key);

        self::assertEquals($text, $encryptor->decryptRaw($encrypted, $key));
    }
}
