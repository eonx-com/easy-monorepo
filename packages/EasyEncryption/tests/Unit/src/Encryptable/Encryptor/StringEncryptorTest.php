<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Encryptor;

use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Encryptable\Encryptor\StringEncryptor;
use EonX\EasyEncryption\Tests\Unit\AbstractSymfonyTestCase;
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
     * @see testItSucceedsWithMultibyteCharacterCutBetweenChunks
     */
    public static function provideMultibyteText(): iterable
    {
        yield 'a 2-byte character' => [
            'text' => 'José, café, naïve',
        ];

        yield 'a 3-byte character' => [
            'text' => 'It’s the “best” offer',
        ];

        yield 'a 4-byte character' => [
            'text' => 'Hello 👋🏻 there 🙂',
        ];

        yield 'a serialized string' => [
            'text' => \serialize(['body' => '<p>It’s José’s café</p>']),
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
        $encryptor = $container->get(EncryptorInterface::class);
        $sut = new StringEncryptor($encryptor, 'app', $maxChunkSize);

        $encryptedText = $sut->encrypt($text);
        $decryptedText = $sut->decrypt($encryptedText->value);

        self::assertSame($text, $decryptedText);
        self::assertSame('app', $encryptedText->encryptionKeyName);
        self::assertTrue(\str_starts_with($encryptedText->value, 'chunked:'));
        self::assertCount($expectedChunksCount, \explode(',', $encryptedText->value));
    }

    #[DataProvider('provideMultibyteText')]
    public function testItSucceedsWithMultibyteCharacterCutBetweenChunks(string $text): void
    {
        $container = $this->getKernel()
            ->getContainer();
        $encryptor = $container->get(EncryptorInterface::class);

        // Chunks are cut by bytes, so every size from 1 to 7 cuts some multibyte character
        for ($maxChunkSize = 1; $maxChunkSize <= 7; $maxChunkSize++) {
            $sut = new StringEncryptor($encryptor, 'app', $maxChunkSize);

            $decryptedText = $sut->decrypt($sut->encrypt($text)->value);

            self::assertSame($text, $decryptedText, \sprintf('Max chunk size %d', $maxChunkSize));
        }
    }

    #[DataProvider('provideShortText')]
    public function testItSucceedsWithShortText(string $text, int $maxChunkSize): void
    {
        $container = $this->getKernel()
            ->getContainer();
        $encryptor = $container->get(EncryptorInterface::class);
        $sut = new StringEncryptor($encryptor, 'app', $maxChunkSize);

        $encryptedText = $sut->encrypt($text);
        $decryptedText = $sut->decrypt($encryptedText->value);

        self::assertSame($text, $decryptedText);
        self::assertSame('app', $encryptedText->encryptionKeyName);
        self::assertFalse(\str_starts_with($encryptedText->value, 'chunked:'));
        self::assertFalse(\str_contains($encryptedText->value, ','));
    }
}
