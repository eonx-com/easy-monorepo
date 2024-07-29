<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Common\Encryptor;

use EonX\EasyEncryption\Common\Encryptor\Encryptor;
use EonX\EasyEncryption\Common\Enum\EncryptionKeyOption;
use EonX\EasyEncryption\Common\Factory\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Common\Provider\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Common\Resolver\SimpleEncryptionKeyResolver;
use EonX\EasyEncryption\Tests\Stub\Resolver\EncryptionKeyResolverStub;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;
use ParagonIE\Halite\KeyFactory;
use PHPUnit\Framework\Attributes\DataProvider;

final class EncryptorTest extends AbstractUnitTestCase
{
    /**
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \SodiumException
     *
     * @see testEncrypt
     */
    public static function provideEncryptData(): iterable
    {
        $message = 'My message';

        yield 'From resolver encryption key as string' => [
            $message,
            null,
            [
                new EncryptionKeyResolverStub([
                    'app' => KeyFactory::generateEncryptionKey()->getRawKeyMaterial(),
                ]),
            ],
        ];

        yield 'From resolver encryption secret key as string' => [
            $message,
            null,
            [
                new EncryptionKeyResolverStub([
                    'app' => KeyFactory::generateEncryptionKeyPair()
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
                    'app' => [
                        EncryptionKeyOption::Key->value => 'IP70gUSWQ3qhl1Nf',
                        EncryptionKeyOption::Salt->value => 'xOI68z0AeksgNYAm',
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
    public static function provideEncryptRawData(): iterable
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
                EncryptionKeyOption::Key->value => 'IP70gUSWQ3qhl1Nf',
                EncryptionKeyOption::Salt->value => 'xOI68z0AeksgNYAm',
            ],
        ];

        $encryptionKeyPair = KeyFactory::generateEncryptionKeyPair();
        yield 'Direct public + secret as array' => [
            $message,
            [
                EncryptionKeyOption::PublicKey->value => $encryptionKeyPair->getPublicKey()
                    ->getRawKeyMaterial(),
                EncryptionKeyOption::SecretKey->value => $encryptionKeyPair->getSecretKey()
                    ->getRawKeyMaterial(),
            ],
        ];

        yield 'Direct key + salt (same) as array' => [
            $message,
            [
                EncryptionKeyOption::Key->value => 'IP70gUSWQ3qhl1Nf',
                EncryptionKeyOption::Salt->value => 'IP70gUSWQ3qhl1Nf',
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
     * @param \EonX\EasyEncryption\Common\Resolver\EncryptionKeyResolverInterface[]|null $resolvers
     */
    #[DataProvider('provideEncryptData')]
    public function testEncrypt(string $text, mixed $key = null, ?array $resolvers = null): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $keyProvider = new DefaultEncryptionKeyProvider($keyFactory, $resolvers ?? []);
        $encryptor = new Encryptor($keyFactory, $keyProvider);

        $encrypted = $encryptor->encrypt($text, $key);

        self::assertEquals($text, (string)$encryptor->decrypt($encrypted));
    }

    /**
     * @param \EonX\EasyEncryption\Common\Resolver\EncryptionKeyResolverInterface[]|null $resolvers
     */
    #[DataProvider('provideEncryptRawData')]
    public function testEncryptRaw(string $text, mixed $key = null, ?array $resolvers = null): void
    {
        $keyFactory = new DefaultEncryptionKeyFactory();
        $keyProvider = new DefaultEncryptionKeyProvider($keyFactory, $resolvers ?? []);
        $encryptor = new Encryptor($keyFactory, $keyProvider);

        $encrypted = $encryptor->encryptRaw($text, $key);

        self::assertEquals($text, $encryptor->decryptRaw($encrypted, $key));
    }
}
