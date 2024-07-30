<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Common\Encryptor;

use EonX\EasyEncryption\Common\Exception\CouldNotDecryptException;
use EonX\EasyEncryption\Common\Exception\CouldNotEncryptException;
use EonX\EasyEncryption\Common\Exception\EasyEncryptionExceptionInterface;
use EonX\EasyEncryption\Common\ValueObject\DecryptedString;
use EonX\EasyEncryption\Common\ValueObject\DecryptedStringInterface;
use ParagonIE\ConstantTime\Encoding;
use Throwable;

abstract class AbstractEncryptor implements EncryptorInterface
{
    private const DEFAULT_KEY_NAME = 'app';

    private const ENCRYPTED_KEY_NAME = 'keyName';

    private const ENCRYPTED_KEY_VALUE = 'value';

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

    public function decryptRaw(
        string $text,
        null|array|string $key = null,
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

    public function encryptRaw(
        string $text,
        null|array|string $key = null,
    ): string {
        return $this->execSafely(CouldNotEncryptException::class, fn (): string => $this->doEncrypt($text, $key, true));
    }

    abstract protected function doDecrypt(
        string $text,
        null|array|string $key,
        bool $raw,
    ): string;

    abstract protected function doEncrypt(
        string $text,
        null|array|string $key,
        bool $raw,
    ): string;

    /**
     * @phpstan-template T of \Throwable
     *
     * @phpstan-param class-string<T> $throwableClass
     *
     * @throws T
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
