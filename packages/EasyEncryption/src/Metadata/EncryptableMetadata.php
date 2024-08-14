<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Metadata;

use EonX\EasyEncryption\Attributes\EncryptableField;
use ReflectionClass;

final class EncryptableMetadata implements EncryptableMetadataInterface
{
    private array $metadata = [];

    public function getEncryptableFieldNames(string|object $entity): array
    {
        $entityClass = \is_object($entity) ? $entity::class : $entity;

        if (isset($this->metadata[$entityClass][__FUNCTION__])) {
            return $this->metadata[$entityClass][__FUNCTION__];
        }

        $encryptedFields = [];
        $reflectionClass = new ReflectionClass($entityClass);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            foreach ($reflectionProperty->getAttributes(EncryptableField::class) as $reflectionAttribute) {
                /** @var \EonX\EasyEncryption\Attributes\EncryptableField $reflectionAttributeInstance */
                $reflectionAttributeInstance = $reflectionAttribute->newInstance();
                $encryptedFields[$reflectionProperty->getName()] = $reflectionAttributeInstance->getFieldName()
                    ?? $reflectionProperty->getName();

                break;
            }
        }

        $this->metadata[$entityClass][__FUNCTION__] = $encryptedFields;

        return $encryptedFields;
    }
}
