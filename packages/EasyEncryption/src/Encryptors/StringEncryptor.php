<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptors;

use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\ValueObjects\EncryptedText;
use InvalidArgumentException;

final class StringEncryptor implements StringEncryptorInterface
{
    private const CHUNKED_TEXT_PREFIX = 'chunked:';

    private const DEFAULT_ENCODING = 'UTF-8';

    private const TEXT_CHUNKS_SEPARATOR = ',';

    /**
     * @phpstan-var int<1, max>
     */
    private int $maxChunkSize;

    public function __construct(
        private EncryptorInterface $encryptor,
        private string $encryptionKeyName,
        int $maxChunkSize,
    ) {
        if ($maxChunkSize < 1) {
            throw new InvalidArgumentException('Max chunk size must be greater or equal to 1.');
        }

        $this->maxChunkSize = $maxChunkSize;
    }

    public function decrypt(string $text): string
    {
        if (\str_starts_with($text, self::CHUNKED_TEXT_PREFIX) === false) {
            return $this->doDecrypt($text);
        }

        $text = \str_replace(self::CHUNKED_TEXT_PREFIX, '', $text);
        $textChunks = \explode(self::TEXT_CHUNKS_SEPARATOR, $text);
        $decryptedTextChunks = [];

        foreach ($textChunks as $textChunk) {
            $decryptedTextChunks[] = $this->doDecrypt($textChunk);
        }

        return \implode('', $decryptedTextChunks);
    }

    public function encrypt(string $text): EncryptedText
    {
        if (\strlen($text) <= $this->maxChunkSize) {
            return new EncryptedText($this->encryptionKeyName, $this->doEncrypt($text));
        }

        /** @var string[] $textChunks */
        $textChunks = \str_split($text, $this->maxChunkSize);
        $encryptedTextChunks = [];

        foreach ($textChunks as $textChunk) {
            $encryptedTextChunks[] = $this->doEncrypt($textChunk);
        }

        return new EncryptedText(
            $this->encryptionKeyName,
            self::CHUNKED_TEXT_PREFIX . \implode(self::TEXT_CHUNKS_SEPARATOR, $encryptedTextChunks)
        );
    }

    private function doDecrypt(string $text): string
    {
        return \mb_convert_encoding((string)$this->encryptor->decrypt($text), self::DEFAULT_ENCODING);
    }

    private function doEncrypt(string $text): string
    {
        return \mb_convert_encoding($this->encryptor->encrypt($text, $this->encryptionKeyName), self::DEFAULT_ENCODING);
    }
}
