<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Metadata;

interface EncryptableMetadataInterface
{
    /**
     * @param class-string|object $entity
     *
     * @return array<string, string>
     */
    public function getEncryptableFieldNames(string|object $entity): array;

    /**
     * @param class-string|object $entity
     *
     * @return \EonX\EasyEncryption\Encryptable\Enum\HashNormalization[]|null
     */
    public function getHashNormalizationsForField(string|object $entity, string $propertyName): ?array;
}
