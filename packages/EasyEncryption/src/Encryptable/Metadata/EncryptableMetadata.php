<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Metadata;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;
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
                /** @var \EonX\EasyEncryption\Encryptable\Attribute\EncryptableField $reflectionAttributeInstance */
                $reflectionAttributeInstance = $reflectionAttribute->newInstance();
                $encryptedFields[$reflectionProperty->getName()] = $reflectionAttributeInstance->getFieldName()
                    ?? $reflectionProperty->getName();

                break;
            }
        }

        $this->metadata[$entityClass][__FUNCTION__] = $encryptedFields;

        return $encryptedFields;
    }

    public function getHashNormalisationsForField(string|object $entity, string $propertyName): ?array
    {
        $entityClass = \is_object($entity) ? $entity::class : $entity;

        if (\array_key_exists($propertyName, $this->metadata[$entityClass][__FUNCTION__] ?? [])) {
            return $this->metadata[$entityClass][__FUNCTION__][$propertyName];
        }

        $hashNormalisations = null;
        $reflectionClass = new ReflectionClass($entityClass);

        if ($reflectionClass->hasProperty($propertyName)) {
            $reflectionProperty = $reflectionClass->getProperty($propertyName);

            foreach ($reflectionProperty->getAttributes(EncryptableField::class) as $reflectionAttribute) {
                /** @var \EonX\EasyEncryption\Encryptable\Attribute\EncryptableField $reflectionAttributeInstance */
                $reflectionAttributeInstance = $reflectionAttribute->newInstance();
                $hashNormalisations = $reflectionAttributeInstance->getHashNormalisations();

                break;
            }
        }

        $this->metadata[$entityClass][__FUNCTION__][$propertyName] = $hashNormalisations;

        return $hashNormalisations;
    }
}
