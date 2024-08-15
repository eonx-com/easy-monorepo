<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Encryptable;

interface EncryptableInterface
{
    /**
     * @param callable(string): string $decryptor
     */
    public function decrypt(callable $decryptor): void;

    /**
     * @param callable(string): \EonX\EasyEncryption\Encryptable\ValueObject\EncryptedString $encryptor
     * @param callable(string): string $hashCalculator
     */
    public function encrypt(callable $encryptor, callable $hashCalculator): void;
}
