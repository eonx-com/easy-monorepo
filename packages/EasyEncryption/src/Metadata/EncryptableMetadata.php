<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Metadata;

use EonX\EasyEncryption\Attribute\EncryptableField;
use ReflectionClass;

final class EncryptableMetadata
{
    private array $metadata = [];

    /**
     * @param class-string|object $entity
     *
     * @return array<string, string>
     */
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
                /** @var \EonX\EasyEncryption\Attribute\EncryptableField $reflectionAttributeInstance */
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
