<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptors;

use EonX\EasyEncryption\HashCalculators\HashCalculatorInterface;
use EonX\EasyEncryption\Interfaces\EncryptableInterface;
use EonX\EasyEncryption\ValueObjects\EncryptedText;

final class ObjectEncryptor
{
    public function __construct(
        private StringEncryptor $encryptor,
        private HashCalculatorInterface $hashCalculator,
    ) {
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
