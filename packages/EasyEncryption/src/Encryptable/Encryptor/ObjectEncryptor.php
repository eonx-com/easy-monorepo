<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Encryptor;

use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface;
use EonX\EasyEncryption\Encryptable\Hasher\EncryptableFieldHasherInterface;
use EonX\EasyEncryption\Encryptable\ValueObject\EncryptedString;

final readonly class ObjectEncryptor implements ObjectEncryptorInterface
{
    public function __construct(
        private StringEncryptorInterface $stringEncryptor,
        private EncryptableFieldHasherInterface $fieldHasher,
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
            fn (string $entityClass, string $field, string $value): string
                => $this->fieldHasher->hashForField($entityClass, $field, $value)
        );
    }
}
