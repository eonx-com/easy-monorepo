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
}
