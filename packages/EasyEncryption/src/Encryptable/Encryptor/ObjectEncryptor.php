<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Encryptor;

use EonX\EasyEncryption\Common\ValueObject\EncryptedString;
use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;

final readonly class ObjectEncryptor implements ObjectEncryptorInterface
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
            fn (string $value): EncryptedString => $this->stringEncryptor->encrypt($value),
            fn (string $value): string => $this->hashCalculator->calculate($value)
        );
    }
}
