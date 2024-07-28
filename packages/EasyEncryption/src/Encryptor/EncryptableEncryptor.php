<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptor;

use EonX\EasyEncryption\HashCalculator\HashCalculatorInterface;
use EonX\EasyEncryption\Interfaces\EncryptableInterface;
use EonX\EasyEncryption\ValueObject\EncryptedText;

final class EncryptableEncryptor
{
    public function __construct(private Encryptor $encryptor, private HashCalculatorInterface $hashCalculator)
    {
    }

    public function decrypt(EncryptableInterface $encryptable): void
    {
        $encryptable->decrypt(fn (string $value): string => $this->encryptor->decrypt($value));
    }

    public function encrypt(EncryptableInterface $encryptable): void
    {
        $encryptable->encrypt(
            fn (string $value): EncryptedText => $this->encryptor->encrypt($value),
            fn (string $value): string => $this->hashCalculator->calculate($value)
        );
    }
}
