<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Metadata;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;
use LogicException;
use ReflectionClass;
use ReflectionProperty;

final class EncryptableMetadata implements EncryptableMetadataInterface
{
    private array $metadata = [];

    /**
     * The property as PHP resolves it for the class: declared on the class or on the closest parent. Unlike
     * ReflectionClass::getProperty(), it also finds a private property of a parent class
     *
     * @param class-string $class
     */
    public static function getProperty(string $class, string $propertyName): ?ReflectionProperty
    {
        $reflectionClass = new ReflectionClass($class);

        do {
            if ($reflectionClass->hasProperty($propertyName)) {
                return $reflectionClass->getProperty($propertyName);
            }

            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass !== false);

        return null;
    }

    public function getEncryptableFieldNames(string|object $entity): array
    {
        $entityClass = \is_object($entity) ? $entity::class : $entity;

        if (isset($this->metadata[$entityClass][__FUNCTION__])) {
            return $this->metadata[$entityClass][__FUNCTION__];
        }

        $encryptedFields = [];
        $declaringClasses = [];
        $reflectionClass = new ReflectionClass($entityClass);

        // ReflectionClass::getProperties() skips private properties of parent classes, so each class is read
        do {
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                if ($reflectionProperty->getDeclaringClass()->getName() !== $reflectionClass->getName()) {
                    continue;
                }

                $propertyName = $reflectionProperty->getName();
                $encryptableField = self::getEncryptableField($reflectionProperty);

                // Redeclared in a child class: a protected or public property is the same property, and the child
                // declaration wins as before. A private one is another value that shares the name
                if (isset($declaringClasses[$propertyName])) {
                    if ($encryptableField !== null && $reflectionProperty->isPrivate()) {
                        throw new LogicException(\sprintf(
                            'The encryptable private property "%s" of "%s" is shadowed by the property of "%s"'
                            . ' in the entity "%s". Rename one of them.',
                            $propertyName,
                            $reflectionClass->getName(),
                            $declaringClasses[$propertyName],
                            $entityClass
                        ));
                    }

                    continue;
                }

                $declaringClasses[$propertyName] = $reflectionClass->getName();

                if ($encryptableField !== null) {
                    $encryptedFields[$propertyName] = $encryptableField->getFieldName() ?? $propertyName;
                }
            }

            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass !== false);

        $this->metadata[$entityClass][__FUNCTION__] = $encryptedFields;

        return $encryptedFields;
    }

    public function getHashNormalizationsForField(string|object $entity, string $propertyName): ?array
    {
        $entityClass = \is_object($entity) ? $entity::class : $entity;

        if (\array_key_exists($propertyName, $this->metadata[$entityClass][__FUNCTION__] ?? [])) {
            return $this->metadata[$entityClass][__FUNCTION__][$propertyName];
        }

        $reflectionProperty = self::getProperty($entityClass, $propertyName);
        $hashNormalizations = $reflectionProperty !== null
            ? self::getEncryptableField($reflectionProperty)?->getHashNormalizations()
            : null;

        $this->metadata[$entityClass][__FUNCTION__][$propertyName] = $hashNormalizations;

        return $hashNormalizations;
    }

    private static function getEncryptableField(ReflectionProperty $reflectionProperty): ?EncryptableField
    {
        foreach ($reflectionProperty->getAttributes(EncryptableField::class) as $reflectionAttribute) {
            return $reflectionAttribute->newInstance();
        }

        return null;
    }
}
