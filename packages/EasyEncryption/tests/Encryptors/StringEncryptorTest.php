<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Encryptors;

use EonX\EasyEncryption\Encryptor;
use EonX\EasyEncryption\Encryptors\StringEncryptor;
use EonX\EasyEncryption\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StringEncryptorTest extends AbstractSymfonyTestCase
{
    /**
     * @see testItSucceedsWithLongText
     */
    public static function provideLongText(): iterable
    {
        yield 'a text in latin' => [
            'text' => 'text-to-encrypt',
            'maxChunkSize' => 6,
            'expectedChunksCount' => 3,
        ];

        yield 'a text in cyrillic' => [
            'text' => 'абвгдеёжзийклмн',
            'maxChunkSize' => 6,
            'expectedChunksCount' => 5,
        ];

        yield 'a text with emoji' => [
            'text' => 'Hello 👋🏻How are you? 🙂',
            'maxChunkSize' => 25,
            'expectedChunksCount' => 2,
        ];
    }

    /**
     * @see testItSucceedsWithShortText
     */
    public static function provideShortText(): iterable
    {
        yield 'a text that smaller than the chunk size' => [
            'text' => 'text-to-encrypt',
            'maxChunkSize' => 20,
        ];

        yield 'a text that equals to the chunk size' => [
            'text' => 'text-to-encrypt',
            'maxChunkSize' => 15,
        ];
    }

    #[DataProvider('provideLongText')]
    public function testItSucceedsWithLongText(string $text, int $maxChunkSize, int $expectedChunksCount): void
    {
        $container = $this->getKernel()
            ->getContainer();
        $encryptor = $container->get(Encryptor::class);
        $sut = new StringEncryptor($encryptor, 'app', $maxChunkSize);

        $encryptedText = $sut->encrypt($text);
        $decryptedText = $sut->decrypt($encryptedText->value);

        self::assertSame($text, $decryptedText);
        self::assertSame('app', $encryptedText->encryptionKeyName);
        self::assertTrue(\str_starts_with($encryptedText->value, 'chunked:'));
        self::assertCount($expectedChunksCount, \explode(',', $encryptedText->value));
    }

    #[DataProvider('provideShortText')]
    public function testItSucceedsWithShortText(string $text, int $maxChunkSize): void
    {
        $container = $this->getKernel()
            ->getContainer();
        $encryptor = $container->get(Encryptor::class);
        $sut = new StringEncryptor($encryptor, 'app', $maxChunkSize);

        $encryptedText = $sut->encrypt($text);
        $decryptedText = $sut->decrypt($encryptedText->value);

        self::assertSame($text, $decryptedText);
        self::assertSame('app', $encryptedText->encryptionKeyName);
        self::assertFalse(\str_starts_with($encryptedText->value, 'chunked:'));
        self::assertFalse(\str_contains($encryptedText->value, ','));
    }
}
