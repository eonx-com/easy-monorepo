<?php

declare(strict_types=1);

namespace EonX\EasyEncryption;

use EonX\EasyEncryption\Exceptions\CouldNotDecryptException;
use EonX\EasyEncryption\Exceptions\CouldNotEncryptException;
use EonX\EasyEncryption\Interfaces\DecryptedStringInterface;
use EonX\EasyEncryption\Interfaces\EasyEncryptionExceptionInterface;
use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use EonX\EasyEncryption\ValueObjects\DecryptedString;
use ParagonIE\ConstantTime\Encoding;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Throwable;

abstract class AbstractEncryptor implements EncryptorInterface
{
    public function __construct(
        private readonly ?string $defaultKeyName = null,
    ) {
    }

    public function decrypt(string $text): DecryptedStringInterface
    {
        $toDecrypt = $this->execSafely(CouldNotDecryptException::class, static function () use ($text): array {
            $toDecryptArray = \json_decode(Encoding::base64Decode($text), true);

            return \is_array($toDecryptArray) ? $toDecryptArray : [];
        });

        if (isset($toDecrypt[self::ENCRYPTED_KEY_NAME], $toDecrypt[self::ENCRYPTED_KEY_VALUE]) === false) {
            throw new CouldNotDecryptException('Given encrypted text has invalid structure');
        }

        return $this->execSafely(
            CouldNotDecryptException::class,
            function () use ($toDecrypt): DecryptedStringInterface {
                $keyName = $toDecrypt[self::ENCRYPTED_KEY_NAME];

                return new DecryptedString(
                    $this->doDecrypt($toDecrypt[self::ENCRYPTED_KEY_VALUE], $keyName, false),
                    $keyName
                );
            }
        );
    }

    /**
     * @param mixed[]|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair|null $key
     */
    public function decryptRaw(
        string $text,
        null|array|string|EncryptionKey|EncryptionKeyPair $key = null,
    ): string {
        return $this->execSafely(CouldNotDecryptException::class, fn (): string => $this->doDecrypt($text, $key, true));
    }

    public function encrypt(string $text, ?string $keyName = null): string
    {
        return $this->execSafely(CouldNotEncryptException::class, function () use ($text, $keyName): string {
            $keyName = $this->getKeyName($keyName);

            return Encoding::base64Encode((string)\json_encode([
                self::ENCRYPTED_KEY_NAME => $keyName,
                self::ENCRYPTED_KEY_VALUE => $this->doEncrypt($text, $keyName, false),
            ]));
        });
    }

    /**
     * @param mixed[]|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair|null $key
     */
    public function encryptRaw(
        string $text,
        null|array|string|EncryptionKey|EncryptionKeyPair $key = null,
    ): string {
        return $this->execSafely(CouldNotEncryptException::class, fn (): string => $this->doEncrypt($text, $key, true));
    }

    /**
     * @param mixed[]|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair|null $key
     */
    abstract protected function doDecrypt(
        string $text,
        null|array|string|EncryptionKey|EncryptionKeyPair $key,
        bool $raw,
    ): string;

    /**
     * @param mixed[]|string|\ParagonIE\Halite\Symmetric\EncryptionKey|\ParagonIE\Halite\EncryptionKeyPair|null $key
     */
    abstract protected function doEncrypt(
        string $text,
        null|array|string|EncryptionKey|EncryptionKeyPair $key,
        bool $raw,
    ): string;

    /**
     * @throws T
     *
     * @phpstan-param class-string<T> $throwableClass
     *
     * @phpstan-template T of \Throwable
     */
    protected function execSafely(string $throwableClass, callable $func): mixed
    {
        try {
            return $func();
        } catch (Throwable $throwable) {
            throw $throwable instanceof EasyEncryptionExceptionInterface
                ? $throwable
                : new $throwableClass($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    protected function getKeyName(?string $keyName = null): string
    {
        return $keyName ?? $this->defaultKeyName ?? self::DEFAULT_KEY_NAME;
    }
}
