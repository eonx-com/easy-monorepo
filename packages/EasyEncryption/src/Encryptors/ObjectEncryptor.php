<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptors;

use EonX\EasyEncryption\HashCalculators\HashCalculatorInterface;
use EonX\EasyEncryption\Interfaces\EncryptableInterface;
use EonX\EasyEncryption\ValueObjects\EncryptedText;

final class ObjectEncryptor implements ObjectEncryptorInterface
{
    public function __construct(
        private StringEncryptorInterface $stringEncryptor,
        private HashCalculatorInterface $hashCalculator,
    ) {
    }

    public function decrypt(EncryptableInterface $encryptable): void
    {
        $encryptable->decrypt(fn (string $value): string => $this->stringEncryptor->decrypt($value));
    }

    public function encrypt(EncryptableInterface $encryptable): void
    {
        $encryptable->encrypt(
            fn (string $value): EncryptedText => $this->stringEncryptor->encrypt($value),
            fn (string $value): string => $this->hashCalculator->calculate($value)
        );
    }
}
