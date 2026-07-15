<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Hasher;

interface EncryptableFieldHasherInterface
{
    /**
     * @param class-string $entityClass
     */
    public function hashForField(string $entityClass, string $field, string $value): string;
}
